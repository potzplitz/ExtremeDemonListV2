<?php

class Account implements BaseScript {
    public function init() {
        MainController::setTitle("Login");

        if(ROUTE == "/account/login" && empty(INS["submit_login"])) {
            $this->show_login();

        } else if(ROUTE == "/account/login" && !empty(INS["submit_login"])) {
            $this->login();
        }
    }

    public function show_login($error = "") {
        $Template = new Template();

        $Template->load_template("account/login.php");
        $Template->load_hash([
            "ERROR" => $error,
            "DISP_ERROR" => (strlen($error) > 0 ? "" : "hiddenIp"),
        ]);
        $Template->compile_template();
        $Template->show_template();
    }

    public function login() {
        $DB = new Database();

        $query = "SELECT * from users where (username = :username or email = :email)";
        $binds = [
            "username" => INS['username'],
            "email"    => INS['username']
        ];

        $DB->query2($query, $binds);

        if(!($DB->rows >= 1)) {
            $this->show_login("Username or Email was not found!");
            return;
        }

        if(password_verify(INS['password'], $DB->RSArray[0]['password'])) {
            header("Location: " . URL_HTBASE);
            return;
        } else {
            $this->show_login("Incorrect Password!");
            return;
        }
    }
}