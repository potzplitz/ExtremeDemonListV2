<?php
require("env.php");

error_reporting(E_ALL);
ini_set('display_errors', getenv("SERVER") == "dev" ? 1 : 0);
ini_set('log_errors', 1);

if(getenv("SERVER") == 'dev') {
    require("DevTools.php");
}

// == Require important Classes === \\
require("Database.php");
require("Template.php");
require("MainController.php");
require("Session.php");
require("BaseScript.php");
require("HelperFunctions.php");
require("User.php");
require("RobApi.php");

// === Important Defines === \\
define("DIR_ROOT", $_SERVER['DOCUMENT_ROOT']);
define("REQUESTER_IP", $_SERVER['REMOTE_ADDR']);
define("URL_HTBASE", "https://" . $_SERVER['HTTP_HOST'] . "/");
define("ROUTE", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
define("MODE", $_REQUEST['mode'] ?? '');
define("INS", $_REQUEST);
define("DIR_ASSETS", DIR_ROOT . "/assets/");