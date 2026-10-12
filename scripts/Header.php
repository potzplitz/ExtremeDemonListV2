<?php

class Header implements BaseScript {
    public function init() {
        $this->render_header();
    }

    private function render_header() {
        $Template = new Template();
        $Template->load_template("header/header.php");
        $Template->load_hash([]);
        
        $Template->compile_template();
        MainController::setHeader($Template->get_output());
    }
}