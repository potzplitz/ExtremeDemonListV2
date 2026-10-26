<?php

class Account implements BaseScript {
    private $account_mode = null;
    public function init() {
        MainController::setTitle("Login");

        if(ROUTE == "/account/login" && empty(INS["submit_login"]) && !IS_LOGGED_IN) {
            $this->show_login();

        } else if(ROUTE == "/account/login" && !empty(INS["submit_login"]) && !IS_LOGGED_IN) {
            $this->login();
        
        } else if(ROUTE == "/account/register" && empty(INS["submit_register"]) && !IS_LOGGED_IN) {
            $this->show_register();

        } else if(ROUTE == "/account/register" && !empty(INS["submit_register"]) && !IS_LOGGED_IN) {
            $this->register_account();

        } else if(ROUTE == "/api/auth/account/register") {
            die;

        } else if(ROUTE == "/api/auth/account/login") {
            $this->account_mode = "api";
            $this->login();

        } else if(ROUTE == "/api/auth/session/renew") {

        } else {
            header("Location: " . URL_HTBASE);
        }
    }

    private function show_login($error = "") {

        if($this->account_mode == "api") {
            header("Content-Type: application/json");
            echo_json(["error" => $error]);
            return;
        }

        $Template = new Template();

        $Template->load_template("account/login.php");
        $Template->load_hash([
            "ERROR" => $error,
            "DISP_ERROR" => (strlen($error) > 0 ? "" : "hiddenIp"),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function login() {
        $DB = new Database();

        if(empty(INS['username'])) {
            $this->show_login("You have to set a Username/Email!");
            return;
        }

        if(empty(INS["password"])) {
            $this->show_login("You have to set a password!");
            return;
        }

        $query = "SELECT * from users where (username = :username or email = :email)";
        $binds = [
            "username" => INS['username'],
            "email"    => INS['username']
        ];

        $DB->query2($query, $binds);

        if(!($DB->rows >= 1)) {
            $this->show_login("Username or Email was not found or the Password was incorrect!");
            return;
        }

        if(password_verify(INS['password'], $DB->RSArray[0]['password'])) {

            $Session = new Session();
            $Session->create_session($DB->RSArray[0]['user_id']);

            if(isset(INS['remember'])) {
                $Session->create_persistent_session($DB->RSArray[0]['user_id']);
            }

            if($this->account_mode == "api") {
                header("Content-Type: application/json");

                $User = new User(SESS_USERID);
                $userdata = $User->load_user();

                echo_json([
                    "user_id" => SESS_USERID,
                    "username" => $userdata['username'],
                    "session_token" => SESS_IDENT,
                    "persistent_hash" => (isset(INS['remember']) ? $User->get_persistent_token() : null)
                ]);
                return;
            } else {
                header("Location: " . URL_HTBASE);
                return;
            }

        } else {
            $this->show_login("Username or Email was not found or the Password was incorrect!");
            return;

        }
    }

    private function show_register($error = "") {
        $Template = new Template();

        $Template->load_template("account/register.php");
        $Template->load_hash([
            "ERROR" => $error,
            "DISP_ERROR" => (strlen($error) > 0 ? "" : "hiddenIp"),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    private function register_account() {
        $DB = new Database();

        if(empty(INS["username"])) {
            $this->show_register("You have to set a username!");
            return;
        }

        if(empty(INS["email"])) {
            $this->show_register("You have to set an Email!");
            return;
        }

        if(empty(INS["password"])) {
            $this->show_register("You have to set a password!");
            return;
        }

        if(INS['password'] != INS['password_confirm']) {
            $this->show_register("The passwords didn't match!");
            return;
        }

        if(strlen(INS['password']) < 12) {
            $this->show_register("Your Password must be at least 12 Characters long!");
            return;
        }

        if($this->isUsernameUnique(INS['username'])) {
            $this->show_register("This username is already in use!");
            return;
        }

        $password_hash = password_hash(INS['password'], PASSWORD_ARGON2ID);

        $query = "INSERT into users (username, email, password, lastlog, first_login) values (:username, :email, :password, current_timestamp, current_timestamp)";
        $binds = [
            "username" => INS['username'],
            "email" => INS["email"],
            "password" => $password_hash
        ];

        // TODO Email verification

        $DB->query2($query, $binds);
        header("Location: " . URL_HTBASE . "account/login?message=Account creation successful! You can login now.");
    }

    private function isUsernameUnique($username):bool {
        $DB = new Database();

        $query = "SELECT count(1) as anz from users where username = :username";
        $binds = [
            "username" => $username
        ];
        $DB->query2($query, $binds);

        return ($DB->RSArray[0]['anz'] > 0);
    }
}