<?php

class Start implements BaseScript {
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);
        $this->show();
    }

    private function show() {
        $Template = new Template();
        $Template->load_template("start/start.php");
        $Template->load_hash([
            "RANDOM_LEVEL" => $this->get_random_level(),
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
}