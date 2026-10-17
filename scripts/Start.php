<?php

class Start implements BaseScript {
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);

        // $this->get_recommended_levels();
        
        // if(IS_LOGGED_IN) {
            $this->show();
        // } else {
        //     $this->show_not_logged_in();
        // }
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
        $position_spread_up = 150;
        $position_spread_down = 200;
        $number_recommendations = 50;

        $query = "WITH top_tags as (select t.tag, count(*) as anzahl from levels a cross join lateral json_array_elements_text(a.tags) as t(tag) ";
        $query .= "where exists (select 1 from levelrecords c where c.level_id = a.level_id) group by t.tag order by anzahl desc limit :most_common_tags), ";
        $query .= "avg_position as (select avg(l.position) as avg_pos from levelrecords lr join levels l on l.level_id = lr.level_id), ";
        $query .= "matching_levels as (select distinct on (a.level_id) a.level_id, a.levelname, a.tags, a.position from levels a ";
        $query .= "where exists (select 1 from json_array_elements_text(a.tags) as t(tag) join top_tags tt on tt.tag = t.tag) ";
        $query .= "and a.position between (select avg_pos - :position_spread_down from avg_position) and (select avg_pos + :position_spread_up from avg_position) ";
        $query .= "order by a.level_id, a.position asc) ";
        $query .= "select * from matching_levels order by position asc limit :number_recommendations";

        $binds = [
            "most_common_tags" => $most_common_tags,
            "position_spread_up" => $position_spread_up,
            "position_spread_down" => $position_spread_down,
            "number_recommendations" => $number_recommendations
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
        
        $query = "SELECT distinct(tag) from levels, jsonb_array_elements_text(tags::jsonb) as tag order by tag";
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