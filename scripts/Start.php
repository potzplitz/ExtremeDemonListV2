<?php

class Start implements BaseScript {
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);

        $this->show();
    }

    private function show() {
        $rows = $this->wrap_section();

        $Template = new Template();
        $Template->load_template("start/start.php");
        $Template->load_hash([
            "RANDOM_LEVEL" => $this->get_random_level(),
            "SECTIONS" => $rows,
        ]);
        $Template->compile_template();
        $Template->show_template();
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
}