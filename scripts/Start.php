<?php

class Start implements BaseScript{
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);
        $this->show();
    }

    private function show() {
        $Template = new Template();
        $Template->load_template("start/start.php");
        $Template->load_hash([]);
        $Template->compile_template();
        $Template->show_template();
    }
}