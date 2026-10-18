<?php

class Profile implements BaseScript {
    private $user_id = -1;
    public function init() {

        if(ROUTE == "/account/profile" && IS_LOGGED_IN) {
            $this->user_id = SESS_USERID;
            MainController::setTitle("Your Profile");
            $this->show();

        } else if(str_contains(ROUTE, "/profile/")) {
            $this->user_id = (int)h(MainController::getRouteParam("id"));
            MainController::setTitle($this->resolve_user($this->user_id));
            $this->show();

        } else {
            header("Location: " . URL_HTBASE);
        }
    }

    private function show() {
        $Template = new Template();
    }

    private function resolve_user($user_id) {
        $DB = new Database();

        $query = "SELECT * from users where user_id = :userid";
        $binds = [
            "userid" => $user_id
        ];
        $DB->query2($query, $binds);

        return $DB->RSArray[0]['username'] ?? -1;
    }
}