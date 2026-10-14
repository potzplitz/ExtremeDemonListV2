<?php

class AssetManager implements BaseScript {
    public function init() {
        if(str_contains(ROUTE, "api/profilepicture")) {
            $this->return_profilepicture();
        }
    }

    private function return_profilepicture() {
        $userid = MainController::getRouteParam("id");

        if(!$this->user_exists($userid)) {
            header("Content-Type: application/json");

            $err = ["error" => "User not found!"];
            echo json_encode($err, JSON_PRETTY_PRINT);

        } else if(file_exists(DIR_ASSETS . "profilepictures/" . $userid . ".png")) {
            header('Content-Type: image/png');
            readfile(DIR_ASSETS . "profilepictures/" . $userid . ".png");

        } else {

            header('Content-Type: image/png');
            readfile(DIR_ASSETS . "profilepictures/default.png");
        }
    }

    private function user_exists($userid) {
        $DB = new Database();

        $query = "SELECT count(1) as anz from users where user_id = :userid";
        $binds = [
            "userid" => $userid
        ];

        $DB->query2($query, $binds);

        return ($DB->RSArray[0]['anz'] > 0);
    }
}