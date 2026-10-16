<?php

class Header implements BaseScript {
    public function init() {
        $this->render_header();
    }

    private function render_header() {

        MainController::addJavascript("header/headerdropdown.js");
        MainController::addJavascript("header/burgermenu.js");

        $DB = new Database();

        $query = "SELECT username from users where user_id = :id";
        $binds = [
            "id" => SESS_USERID
        ];

        $DB->query2($query, $binds);

        $username = $DB->RSArray[0]['username'] ?? '';

        $Template = new Template();
        $Template->load_template("header/header.php");
        $Template->load_hash([
            "DISP_LOGINBTN" => ((SESS_USERID == -1) ? "" : "hiddenIp"),
            "DISP_USER" => ((SESS_USERID == -1) ? "hiddenIp" : ""),
            "USERNAME" => h($username),
            "PROFILE_PICTURE" => URL_HTBASE . "api/profilepicture/" . SESS_USERID,
            "BURGERMENU" => $this->render_burger($username),
        ]);
        
        $Template->compile_template();
        MainController::setHeader($Template->get_output());
    }

    private function render_burger($username) {
        $Template = new Template();
        $Template->load_template("header/burgermenu.php");
        $Template->load_hash([
            "DISP_LOGINBTN" => ((SESS_USERID == -1) ? "" : "hiddenIp"),
            "DISP_USER" => ((SESS_USERID == -1) ? "hiddenIp" : ""),
            "USERNAME" => h($username),
            "PROFILE_PICTURE" => URL_HTBASE . "api/profilepicture/" . SESS_USERID,
        ]);
        $Template->compile_template();
        return $Template->get_output();
    }
}