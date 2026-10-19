<?php

class Profile implements BaseScript {
    private $user_id = -1;
    public function init() {

        $bread[] = ["link" => "/", "text" => "Main Page"];

        if(ROUTE == "/account/profile" && IS_LOGGED_IN) {
            $this->user_id = SESS_USERID;
            MainController::setTitle("Your Profile");

            $bread[] = ["link" => "/", "text" => "Your Profile"];

            $this->show();

        } else if(str_contains(ROUTE, "/user/")) {
            $this->user_id = (int)h(MainController::getRouteParam("id"));
            $user = $this->resolve_user($this->user_id)['username'];
            
            MainController::setTitle($user);

            $bread[] = ["link" => "/", "text" => "Profile of " . $user];

            $this->show();

        } else {
            header("Location: " . URL_HTBASE);
        }

        MainController::setBreadcrumb($bread);
    }

    private function show() {

        $user = $this->resolve_user($this->user_id);
        $hardest = $this->get_hardest();
        $hasHardest = !empty($hardest);

        $Template = new Template();
        $Template->load_template("profile/overview.php");
        $Template->load_hash([
            "IMAGE" => URL_HTBASE . "api/profilepicture/" . SESS_USERID,
            "USERNAME" => $user['username'],
            "LASTLOG" => format_date($user['lastlog']),
            "FIRST_LOGIN" => format_date($user['first_login']),
            "LEVEL_ID" => $hardest['level_id'] ?? '',
            "HARDEST_MOD" => $hasHardest ? "" : "gdProfile__hardest--empty",
            "THUMB" => $hasHardest ? "https://levelthumbs.prevter.me/thumbnail/" . $hardest['level_id'] . "/small" : "",
            "POSITION" => $hardest['position'] ?? "",
            "NAME" => $hasHardest ? $hardest['levelname'] : "No AREDL completion yet",
            "COMPLETIONS_AREDL" => $this->get_completions()['aredl'],
            "COMPLETIONS_NCL" => $this->get_completions()['ncl'],
            "PROGRESS_AREDL" => $this->get_progress()['aredl'],
            "PROGRESS_NCL" => $this->get_progress()['ncl'],
            "SKILLSET_TAGS" => $this->render_skillset_tags(),
            "LINK" => "#",
            "LOCALE" => "",
            // "ICONS" => $this->render_profile_icons(),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function get_top_tags() {
        $DB = new Database();

        $most_common_tags = 5;

        // Nicht-Array-Tags (JSON null, Strings, …) als leeres Array behandeln
        $tags_safe = "(case when json_typeof(a.tags) = 'array' then a.tags else '[]'::json end)";

        $query = "SELECT t.tag, count(*) as cnt from levels a cross join lateral json_array_elements_text($tags_safe) as t(tag) ";
        $query .= "where a.level_id in (select level_id from levelrecords where user_id = :userid and progress > 99) ";
        $query .= "and t.tag !~ '^[0-9.]+$' ";
        $query .= "group by t.tag order by cnt desc, t.tag asc limit :most_common_tags";

        $binds = [
            "userid" => $this->user_id,
            "most_common_tags" => $most_common_tags,
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray ?? [];
    }

    private function render_skillset_tags() {
        $tags = $this->get_top_tags();

        if (empty($tags)) {
            return '<span class="gdProfile__infowrapper__info__sub">No completions yet</span>';
        }

        $rows = "";

        foreach ($tags as $tag) {
            $Template = new Template();
            $Template->load_template("profile/skilltag.php");
            $Template->load_hash([
                "TAG" => $tag['tag'],
                "COUNT" => $tag['cnt'],
            ]);
            $Template->compile_template();

            $rows .= $Template->get_output();
        }

        return $rows;
    }

    private function get_completions() {
        $DB = new Database();

        $query = "SELECT
                    COUNT(DISTINCT CASE WHEN a.sart = 'AREDL' THEN a.level_id END) AS aredl,
                    COUNT(DISTINCT CASE WHEN a.sart = 'NCL' THEN a.level_id END) AS ncl
                FROM levelrecords b
                INNER JOIN levels a ON a.level_id = b.level_id
                WHERE b.user_id = :userid
                  AND b.progress > 99";

        $binds = [
            "userid" => $this->user_id
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray[0] ?? [];
    }

    private function get_progress() {
        $DB = new Database();

        $query = "SELECT
                    COUNT(DISTINCT CASE WHEN a.sart = 'AREDL' THEN a.level_id END) AS aredl,
                    COUNT(DISTINCT CASE WHEN a.sart = 'NCL' THEN a.level_id END) AS ncl
                FROM levelrecords b
                INNER JOIN levels a ON a.level_id = b.level_id
                WHERE b.user_id = :userid
                  AND a.sart IN ('AREDL', 'NCL')
                  AND b.progress >= 35
                  AND b.progress < 100";

        $binds = [
            "userid" => $this->user_id
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray[0] ?? [];
    }

    private function get_hardest() {
        $DB = new Database();

        $query = "SELECT b.level_id, a.position, a.levelname from levels a left join levelrecords b on b.level_id = a.level_id 
                    where b.user_id = :userid and a.sart = 'AREDL' and b.progress > 99 order by a.position asc fetch first 1 row only";

        $binds = [
            "userid" => $this->user_id
        ];

        $DB->query2($query, $binds);

        return $DB->RSArray[0] ?? [];
    }

    private function resolve_user($user_id) {
        $DB = new Database();

        $query = "SELECT * from users where user_id = :userid";
        $binds = [
            "userid" => $user_id
        ];
        $DB->query2($query, $binds);

        return $DB->RSArray[0];
    }
}