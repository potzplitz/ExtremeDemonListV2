<?php

class Start implements BaseScript {

    private $changelog_release_id = 0;
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);

        if(ROUTE == "/api/changelog/read" && IS_LOGGED_IN) {
            $this->set_read();
        } else {
            $this->show();
        }
    }

    private function show() {
        $rows = $this->wrap_section();

        $changelog = "";

        if(IS_LOGGED_IN) {
            $changelog = $this->check_changelog_git();
            MainController::addJavascript("start/changelog.js");
        }

        $Template = new Template();
        $Template->load_template("start/start.php");
        $Template->load_hash([
            "RANDOM_LEVEL" => $this->get_random_level(),
            "SECTIONS" => $rows,
            "CHANGELOG" => htmlspecialchars($changelog, ENT_QUOTES),
            "CHANGELOG_SHOW" => $changelog !== "" ? "true" : "false",
            "CHANGELOG_RELEASE_ID" => (string)$this->changelog_release_id
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function set_read() {
        $DB = new Database();

        $query = "INSERT into user_changelog_status (user_id, last_seen_release_id, last_seen_at) values (:user_id, :last_seen_release_id, current_timestamp) on conflict (user_id) do update set last_seen_release_id = excluded.last_seen_release_id, last_seen_at = current_timestamp";
        $binds = [
            "user_id" => SESS_USERID,
            "last_seen_release_id" => INS['release_id']
        ];

        $DB->query2($query, $binds);
    }

    private function wrap_section() {

        $order = ["list", "tags", "recommendations"];

        $sections = [
            "list" => [
                "title" => "Current Top 100",
                "class" => "gdStart__list__section",
                "render" => fn() => $this->render_top(),
                "link" => URL_HTBASE . "extremedemons",
                "require_login" => false,
            ],
            "tags" => [
                "title" => "Browse Tags",
                "class" => "gdStart__tags__section",
                "render" => fn() => $this->render_tags(),
                "link" => URL_HTBASE . "search",
                "require_login" => false,
            ],
            "recommendations" => [
                "title" => "Your Recommendations",
                "class" => "gdStart__list__section",
                "render" => fn() => $this->render_recommendations(),
                "link" => URL_HTBASE . "#",
                "require_login" => true
            ],
        ];

        uksort($sections, fn($a, $b) => array_search($a, $order) <=> array_search($b, $order));

        $rows = "";
        foreach ($sections as $section) {
            if (!$section) {
                continue;
            }

            if($section['require_login'] && !IS_LOGGED_IN) {
                continue;
            }
            
            $Template = new Template();
            $Template->load_template("start/section.php");
            $Template->load_hash([
                "TITLE" => $section['title'],
                "BODY_CLASS" => $section['class'],
                "CONTENT" => $section['render'](),
                "HREF" => $section['link'],
            ]);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function get_random_level() {
        $DB = new Database();

        $query = "SELECT * from levels order by random() desc fetch first 1 row only";
        $DB->query($query);

        return $DB->RSArray[0]['level_id'];
    }

    private function render_recommendations() {
        $Template = new Template();

        $levels = $this->get_recommended_levels();
        
        $rows = "";

        foreach($levels as $level) {
            $Template->load_template("start/list_row.php");
            $Template->load_hash([
                "IMAGE" => "https://levelthumbs.prevter.me/thumbnail/" . $level['level_id'] . "/small",
                "LEVELNAME" => $level['levelname'],
                "POSITION" => $level['position']
            ]);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }

        
        $Template->load_template("start/list.php");
        $Template->load_hash([
            "ROWS" => $rows
        ]);
        $Template->compile_template();
        
        return $Template->get_output();
    }

    private function get_recommended_levels() {
        $DB = new Database;

        $most_common_tags = 5; // top tag appearances
        $hardest_count = 10;
        $position_spread_up = 150;
        $position_spread_down = 100;
        $number_recommendations = 50;

        // Nicht-Array-Tags (JSON null, Strings, …) als leeres Array behandeln
        $tags_safe = "(case when json_typeof(a.tags) = 'array' then a.tags else '[]'::json end)";

        $query = "WITH top_tags as (select t.tag from levels a cross join lateral json_array_elements_text($tags_safe) as t(tag) ";
        $query .= "where a.level_id in (select level_id from levelrecords where user_id = :userid and progress > 99) group by t.tag order by count(*) desc limit :most_common_tags), ";
        $query .= "hardest as (select avg(x.position) as avg_pos from (select distinct l.level_id, l.position from levelrecords lr join levels l on l.level_id = lr.level_id ";
        $query .= "where lr.user_id = :userid and lr.progress > 99 and l.position is not null and l.sart = 'AREDL' order by l.position asc limit :hardest_count) x), ";
        $query .= "candidates as (select a.level_id, a.levelname, a.tags, a.position, ";
        $query .= "(select count(*) from json_array_elements_text($tags_safe) as t(tag) where t.tag in (select tag from top_tags)) as tag_hits, ";
        $query .= "abs(a.position - h.avg_pos) as distance ";
        $query .= "from levels a cross join hardest h ";
        $query .= "where a.sart = 'AREDL' and a.position between h.avg_pos - :position_spread_down and h.avg_pos + :position_spread_up ";
        $query .= "and not exists (select 1 from levelrecords r where r.level_id = a.level_id and r.user_id = :userid and r.progress > 99)) ";
        $query .= "select level_id, levelname, tags, position from (";
        $query .= "select level_id, levelname, tags, position from candidates where tag_hits > 0 ";
        $query .= "order by tag_hits desc, distance asc limit :number_recommendations";
        $query .= ") picked order by position asc";


        $binds = [
            "most_common_tags" => $most_common_tags,
            "hardest_count" => $hardest_count,
            "position_spread_up" => $position_spread_up,
            "position_spread_down" => $position_spread_down,
            "number_recommendations" => $number_recommendations,
            "userid" => SESS_USERID
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray;
    }

    private function render_tags() {
        
        $tags = $this->get_tags();
        $tag_rows = "";

        foreach($tags as $tag) {
            $Template = new Template();
            $Template->load_template("start/tags_tile.php");
            $Template->load_hash([
                "TAG" => $tag['tag']
            ]);
            $Template->compile_template();
            $tag_rows .= $Template->get_output();
        }

        $Template = new Template();
        $Template->load_template("start/tags.php");
        $Template->load_hash([
            "TAGS" => $tag_rows
        ]);
        $Template->compile_template();

        return $Template->get_output();
    }

    private function render_top() {
        
        $levels = $this->get_top();
        $level_rows = "";

        foreach($levels as $level) {
            $Template = new Template();
            $Template->load_template("start/list_row.php");
            $Template->load_hash([
                "LEVELNAME" => $level['levelname'],
                "IMAGE" => "https://levelthumbs.prevter.me/thumbnail/" . $level['level_id'] . "/small",
                "POSITION" => $level['position']
            ]);
            $Template->compile_template();
            $level_rows .= $Template->get_output();
        }

        $Template = new Template();
        $Template->load_template("start/list.php");
        $Template->load_hash([
            "ROWS" => $level_rows
        ]);
        $Template->compile_template();

        return $Template->get_output();
    }

    private function get_tags() {
        $DB = new Database();
        
            $query = "
                    SELECT DISTINCT t.tag AS tag
                    FROM levels l
                    CROSS JOIN LATERAL jsonb_array_elements_text(l.tags::jsonb) AS t(tag)
                    WHERE l.sart = 'AREDL'
                    AND jsonb_typeof(l.tags::jsonb) = 'array'
                    ORDER BY t.tag
                ";
        $DB->query($query);

        return $DB->RSArray;
    }

    private function get_top() {
        $DB = new Database();

        $query = "SELECT * from levels where sart = 'AREDL' order by position asc fetch first 100 rows only";
        $DB->query($query);

        return $DB->RSArray;
    }

    private function show_not_logged_in() {
        $Template = new Template();
        $Template->load_template("start/start_not_logged_in.php");
        $Template->load_hash([
            "RANDOM_LEVEL" => $this->get_random_level(),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function check_changelog_git() { // prüft ob 5 minuten seit letztem aufruf vergangen sind
        $DB = new Database();

        $query = "SELECT last_refresh from git_refresh_status where id = 1";
        $DB->query($query);

        $last_refresh = $DB->RSArray[0]['last_refresh'] ?? null;

        if($last_refresh == null || strtotime($last_refresh) + 300 <= time()) {
            $this->get_changelog_git();
        }

        return $this->load_changelog_git();
    }

    private function load_changelog_git() { 
        $DB = new Database();

        $quLastUpdateSeen = "SELECT last_seen_release_id from user_changelog_status where user_id = :user_id"; // in value steht release_id vom letzten changelog fenster schließen
        $bindsLastUpdateSeen = [
            "user_id" => SESS_USERID
        ];

        $DB->query2($quLastUpdateSeen, $bindsLastUpdateSeen);

        $last_seen = $DB->RSArray[0]['last_seen_release_id'] ?? null;

        $query = "SELECT * from last_site_update order by last_update desc fetch first 10 rows only";
        $DB->query($query);

        $releases = $DB->RSArray;
        $unseen_count = 0;

        foreach($releases as $release) {
            if($last_seen == null || (int)$release['release_id'] != (int)$last_seen) {
                $unseen_count++;
            } else {
                break;
            }
        }

        $quGetReleases = "SELECT * from last_site_update order by deploy_timestamp desc fetch first " . $unseen_count . " rows only";
        $DB->query($quGetReleases);

        $this->changelog_release_id = (int)($DB->RSArray[0]['release_id'] ?? 0);

        $Template = new Template();
        $Template->load_template("start/changelog.php");
        
        $entries = "";
        foreach($DB->RSArray as $rel) {
            $Template = new Template();
            $Template->load_template("start/changelog_entry.php");
            $Template->load_hash([
                "TITLE" => htmlspecialchars($rel['release_name'] ?? ''),
                "TAG" => htmlspecialchars($rel['tag_name'] ?? ''),
                "CHANGES" => $this->format_changelog($rel['changes'])
            ]);
            $Template->compile_template();
            $entries .= $Template->get_output();
        }

        if($entries === "") {
            return "";
        }

        $Template = new Template();
        $Template->load_template("start/changelog.php");
        $Template->load_hash([
            "ENTRIES" => $entries
        ]);
        $Template->compile_template();

        return $Template->get_output();
        
    }

    private function format_changelog($raw) {
        $md = json_decode($raw, true);
        if(!is_string($md)) {
            $md = (string)$raw;
        }

        $html = "";
        $in_list = false;

        foreach(preg_split('/\R/', trim($md)) as $line) {
            $line = trim($line);

            if(str_starts_with($line, '- ')) {
                if(!$in_list) {
                    $html .= "<ul>";
                    $in_list = true;
                }

                $item = $this->format_changelog_inline(substr($line, 2));
                $item = preg_replace('/\s\(([0-9a-f]{7,40}), ([^()]+)\)$/', ' <span class="gdStart__changelog__meta">$1 · $2</span>', $item);

                $html .= "<li>" . $item . "</li>";
                continue;
            }

            if($in_list) {
                $html .= "</ul>";
                $in_list = false;
            }

            if($line !== "") {
                $html .= "<p>" . $this->format_changelog_inline($line) . "</p>";
            }
        }

        if($in_list) {
            $html .= "</ul>";
        }

        return $html;
    }

    private function format_changelog_inline($text) {
        $text = htmlspecialchars($text, ENT_QUOTES);
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/(?<!\w)_(.+?)_(?!\w)/', '<em>$1</em>', $text);
        $text = preg_replace('~(https?://[^\s<]+)~', '<a href="$1" target="_blank" rel="noopener">$1</a>', $text);

        return $text;
    }

    private function get_changelog_git() {
        $DB = new Database();

        $repo_url = GIT_REPO_URL . "?per_page=10";

        //request

        $ch = curl_init($repo_url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/vnd.github+json',
                'User-Agent: ExtremeDemonListV2'
            ]
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $query = "INSERT into log (sart, user_id, value, timestamp) values ('ERR', :user_id, :value, current_timestamp)";
        $binds = [
            "user_id" => SESS_USERID,
            "value" => "Startseite github curl error | code: " . $status . " | response: " . $response
        ];

        $quLog = "UPDATE git_refresh_status set last_refresh = current_timestamp, last_status = 200, last_error = null where id = 1";
        $DB->query($quLog);

        if((int)$status != 200 || $response === false) {

            $DB->query2($query, $binds);

        } else {
            $json_res = json_decode($response, true);

            if(!is_array($json_res)) {
                return;
            }

            $query = "INSERT into last_site_update (user_id, release_name, release_id, last_update, changes, tag_name, deploy_timestamp) values (:user_id, :release_name, :release_id, current_timestamp, :changes, :tag_name, :deploy_timestamp)";
            $quCheck = "SELECT 1 from last_site_update where release_id = :release_id";

            foreach(array_reverse($json_res) as $elem) {
                $binds = [
                    "user_id" => SESS_USERID,
                    "tag_name" => $elem['tag_name'],
                    "release_id" => $elem['id'],
                    "release_name" => $elem['name'],
                    "changes" => json_encode($elem['body']),
                    "deploy_timestamp" => strtotime($elem['created_at'])
                ];

                $bindsCheck = [
                    "release_id" => $elem['id']
                ];

                $DB->query2($quCheck, $bindsCheck);
                if($DB->rows > 0) {
                    continue;
                }

                $DB->query2($query, $binds);
            }
        }
    }
}