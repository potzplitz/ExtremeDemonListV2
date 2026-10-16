<?php

// === Error Reporting === \\
error_reporting(E_ALL);
ini_set('display_errors', 1);

// == Require important Classes === \\
require("env.php");
require("Database.php");
require("Template.php");
require("MainController.php");
require("Session.php");
require("BaseScript.php");
require("HelperFunctions.php");

// === Important Defines === \\
define("DIR_ROOT", $_SERVER['DOCUMENT_ROOT']);
define("REQUESTER_IP", $_SERVER['REMOTE_ADDR']);
define("URL_HTBASE", "https://" . $_SERVER['HTTP_HOST'] . "/");
define("ROUTE", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
define("MODE", $_REQUEST['mode'] ?? '');
define("INS", $_REQUEST);
define("DIR_ASSETS", DIR_ROOT . "/assets/");