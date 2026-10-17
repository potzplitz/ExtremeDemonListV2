<?php
class Logout implements BaseScript {
    public function init() {
        $this->logout();
    }
    private function logout() {
        setcookie("session", "", time() - 3600, "/");
        setcookie("persistent", "", time() - 3600, "/");

        $DB = new Database();

        $query = "UPDATE sessions set valid = 0 where userid = :userid and sess_id = :sess_id";
        $binds = [
            "userid" => SESS_USERID,
            "sess_id" => SESS_ID
        ];
        $DB->query2($query, $binds);

        $query = "UPDATE sessions_persistent set valid = 0 where userid = :userid and persistent_id = :hash";
        $binds = [
            "userid" => SESS_USERID,
            "hash"   => hash_hmac('sha256', $_COOKIE['persistent'] ?? '', getenv("HMAC_SECRET")),
        ];
        $DB->query2($query, $binds);

        header("Location: /");
    }
}