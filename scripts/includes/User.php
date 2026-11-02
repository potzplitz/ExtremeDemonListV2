<?php

class User {
    public array $user = [];
    private $userid = -1;
    private $is_admin = false;
    private $is_logged_in = false;

    public function __construct($userid) {
        $this->userid = $userid;
    }

    public function load_user(): array {
        $DB = new Database();

        if ($this->userid == -1) {
            $this->is_logged_in = false;
            return ["userid" => -1];
        }

        $query = "SELECT * from users where user_id = :userid";
        $binds = [
            "userid" => $this->userid
        ];

        $DB->query2($query, $binds);

        if ($DB->rows <= 0) {
            $this->is_logged_in = false;
            return ["userid" => -1];
        }

        $user = [
            "userid" => $DB->RSArray[0]['user_id'],
            "username" => $DB->RSArray[0]['username'],
            "email" => $DB->RSArray[0]['email']
        ];

        $this->user = $user;
        $this->is_logged_in = true;

        return $user;
    }

    public function isAdmin(): bool {
        return isset($this->user['is_admin']) && (int)$this->user['is_admin'] === 1;
    }

    public function getRank() {
        $DB = new Database();

        $query = "";
    }

    public function get_persistent_token() {
        $DB = new Database();

        $query = "SELECT * from sessions_persistent where userid = :userid and valid = 1 order by expires_at desc fetch first 1 row only";
        $binds = [
            "userid" => $this->userid
        ];

        $DB->query2($query, $binds);
        
        return $DB->RSArray[0]['token_hash'];

    }

    public function isLoggedIn(): bool {
        return $this->is_logged_in;
    }
}