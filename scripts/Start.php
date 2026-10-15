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

        $order = ["tags", "list", "recommended"];
        $rows = "";

        foreach($order as $elem) {
            switch($elem) {
                case "tags":
                    $rows .= $this->render_tags();
                    break;
            }
        }

        $Template = new Template();
        $Template->load_template("start/start.php");
        $Template->load_hash([
            "RANDOM_LEVEL" => $this->get_random_level(),
            "SECTIONS" => $rows,
        ]);
        $Template->compile_template();
        $Template->show_template();
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

    private function get_tags() {
        $DB = new Database();
        
        $query = "SELECT distinct(tag) from levels, jsonb_array_elements_text(tags::jsonb) as tag order by tag";
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