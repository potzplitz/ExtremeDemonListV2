<?php

class Start implements BaseScript{
    public function init() {
        MainController::setTitle("Main Page");
        MainController::setBreadcrumb(["invisible"]);
        $this->show();
    }

    private function show() {
        echo SESS_ID;
    }
}