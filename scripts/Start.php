<?php

class Start implements BaseScript {
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);
        
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

        $order = ["list", "tags"];

        $sections = [
            "list" => [
                "title" => "Current Top 100",
                "class" => "gdStart__list__section",
                "render" => fn() => $this->render_top(),
                "link" => URL_HTBASE . "extremedemons",
            ],
            "tags" => [
                "title" => "Browse Tags",
                "class" => "gdStart__tags__section",
                "render" => fn() => $this->render_tags(),
                "link" => URL_HTBASE . "search",
            ],
        ];

        $sections = array_replace(array_flip($order), $sections);

        $rows = "";

        foreach($sections as $key => $section) {
            $section = $sections[$key] ?? null;
            if (!$section) {
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