<?php

class GDList implements BaseScript {
    public function init() {
        MainController::setTitle("Extreme Demon List");

        $bread[] = ["link" => "/", "text" => "Main Page"];
        $bread[] = ["link" => "#", "text" => "Extreme Demon List"];

        MainController::setBreadcrumb($bread);
    }

    private function show() {
        
    }
}