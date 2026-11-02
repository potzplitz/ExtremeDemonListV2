<?php

class ApiAuth implements BaseScript {
    public function init() {
        $this->check_inputs();
        $this->auth();
    }

    private function check_inputs() {
        $inputs = INS;

        if($_SERVER['REQUEST_METHOD'] !== "POST") {
            header('Allow: POST');
            $this->throw_api_error("Invalid Request Method!");
        }

        if(count($inputs) <= 0) {
            $this->throw_api_error("Bad Request");
        }

        if(!isset($inputs['username'])) {
            $this->throw_api_error("You have to set a username/Email!");
        }

        if(!isset($inputs['password'])) {
            $this->throw_api_error("You have to set a password!");
        }
    }

    private function auth() {
        $DB = new Database();

        $query = "SELECT user_id, username, password from users where username = :username or email = :email";
        $binds = [
            "username" => INS['username'],
            "email"    => INS['username']
        ];

        $DB->query2($query, $binds);

        if(!($DB->rows >= 1)) {
            $this->throw_api_error("Username or Email was not found or the Password was incorrect!");
        }

        if(password_verify(INS['password'], $DB->RSArray[0]['password'])) {
            $sess = $this->create_session(isset(INS['remember']), $DB->RSArray[0]['user_id']);
            $this->return_userinfo($DB->RSArray[0]['user_id'], $sess[0], $sess[1]);

        } else {
            $this->throw_api_error("Username or Email was not found or the Password was incorrect!");
        }
    }

    private function create_session($remember, $user_id) {
        $Session = new Session();
        $sess = $Session->create_session($user_id);

        if($remember) {
            $persistent_sess = $Session->create_persistent_session($user_id);
        }

        return [$sess, $persistent_sess ?? null];
    }

    private function return_userinfo($user_id, $session_token, $persistent_token) {
        $User = new User($user_id);

        $userdata = $User->load_user();

        $returnobj = [
            "success" => "ok",
            "user_id" => $user_id,
            "username" => $userdata['username'],
            "session_token" => $session_token,
            "persistent_hash" => (isset(INS['remember']) ? $persistent_token : null)
        ];

        echo_json($returnobj);
    }

    private function throw_api_error($message) {
        echo_json(["error" => $message]);
        exit();
    }
}