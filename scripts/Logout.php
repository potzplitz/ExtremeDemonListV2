<?php
class Logout implements BaseScript {
    public function init() {
        $this->logout();
    }
    private function logout() {
        setcookie("session", "", time() - 3600, "/");
        setcookie("persistent", "", time() - 3600, "/");

        $DB = new Database();

        $binds = [
            "userid" => SESS_USERID,
            "sess_id" => SESS_ID
        ];

        $query = "UPDATE sessions set valid = 0 where userid = :userid and sess_id = :sess_id";
        $DB->query2($query, $binds);

        $query = "UPDATE sessions_persistent set valid = 0 where userid = :userid and persistent_id = :sess_id";
        $DB->query2($query, $binds);

        header("Location: /");
    }
}