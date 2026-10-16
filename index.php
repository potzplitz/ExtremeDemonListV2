<?php
require_once("scripts/includes/Config.php");
require_once("routemap.php");

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function match_route(string $pattern, string $request): array|false
{
    $regex = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern);
    $regex = '#^' . $regex . '$#';

    if (!preg_match($regex, $request, $matches)) {
        return false;
    }

    // Keep only the named (string-keyed) captures.
    return array_filter(
        $matches,
        fn($key) => is_string($key),
        ARRAY_FILTER_USE_KEY
    );
}

function resolve_route(string $request): ?array
{
    foreach (ROUTEMAP as $pattern => $script) {
        $params = match_route($pattern, $request);
        if ($params !== false) {
            return ['script' => $script, 'params' => $params];
        }
    }
    return null;
}

$route = resolve_route($request);

if ($route === null) {
    require_once("scripts/Error_404.php");
    $Error404 = new Error_404();
    $Error404->show();
    // TODO log failed route
} else {
    $scriptName   = $route['script'];
    $routeParams  = $route['params'];

    $Session = new Session();
    $Session->load_session_data();

    define("IS_LOGGED_IN", SESS_USERID !== -1);

    MainController::setRouteParams($routeParams);

    $scriptPath = file_exists("scripts/" . $scriptName . ".php") ? "scripts/" . $scriptName . ".php" : "scripts/api/" . $scriptName . ".php";

    require_once($scriptPath);

    $script = new $scriptName;
    $script->init();

    if (str_contains($request, "/api")) {
        return;
    }

    MainController::addJavascript("header/breadcrumb.js");

    require_once("scripts/Header.php");
    $Header = new Header();
    $Header->init();

    require_once("scripts/Footer.php");
    $Footer = new Footer();
    $Footer->init();
}

if (!str_contains($request, "/api")) {

    $Template = new Template();
    $Template->load_template("main/main.php");
    $Template->load_hash([
        "TITLE"       => MainController::getTitle(),
        "META_TAGS"   => MainController::getMetaTags(),
        "HEAD_HTML"   => MainController::getHeadHTML() ?? "",
        "ICONS"       => MainController::getIcons(),
        "BODY_HEADER" => MainController::getHeader(),
        "BODY_FOOTER" => MainController::getFooter(),
        "CONTENT"     => MainController::getContent(),
        "SCRIPTS"     => MainController::getJavascript(),
    ]);
    $Template->compile_template();
    echo $Template->get_output();

}