<?php

class MainController {
    private static string $title = "";
    private static string $metaTags = "";
    private static string $headHtml = "";
    private static string $bodyContent = "";
    private static string $headerHtml = "";
    private static string $footerHtml = "";
    private static string $breadcrumb = "";
    private static string $mobile_breadcrumb = "";
    private static string $scriptname = "";
    private static array $routeParams = [];
    private static $scripts = [];
    
    public static function setTitle($title = "") {
        if(!empty($title) || $title == "") {
            self::$title = $title;
        } else {
            throw new InvalidArgumentException("invalid title!");
        }
    }

    public static function addJavascript($script, $options = []) {
        // Default Optionen
        $defaults = [
            'defer' => false,
            'async' => false,
            'external' => false
        ];

        $opts = array_merge($defaults, $options);

        // URL bauen
        $src = $opts['external']
            ? $script
            : URL_HTBASE . "javascript/" . $script;

        // Attribute
        $attr = "";
        if ($opts['defer']) $attr .= " defer";
        if ($opts['async']) $attr .= " async";

        // Duplikate vermeiden
        if (!isset(self::$scripts[$src])) {
            self::$scripts[$src] = "<script src='{$src}'{$attr}></script>";
        }
    }

    public static function getJavascript() {
        return implode("\n", self::$scripts);
    }

    public static function getTitle() {

        if(!isset(self::$title) || self::$title == "") {
            throw new InvalidArgumentException("You have to set a title!");
        }

        return self::$title;
    }

    public static function setMetaTags($tag = "", $type = "") {
        self::$metaTags .= '<meta name="' . htmlspecialchars($type) . '" content="' . htmlspecialchars($tag) . '">' . PHP_EOL;
    }

    public static function getMetaTags() {
        return self::$metaTags;
    }

    public static function setHeadHTML($html = "") {
        self::$headHtml = $html;
    }

    public static function getHeadHTML() {
        return self::$headHtml;
    }

    public static function getIcons() {
        return file_get_contents("scripts/includes/Icons.php");
    }

    public static function setContent($content) {
        if(!isset($content)) {
            throw new InvalidArgumentException("Sitebody Content cannot be empty!");
        }

        self::$bodyContent = $content;
    }

    public static function getContent() {
        return self::$bodyContent;
    }

    public static function setHeader($header_html) {
        self::$headerHtml = $header_html;
    }

    public static function setFooter($footer_html) {
        self::$footerHtml = $footer_html;
    }

    public static function getHeader() {
        return self::$headerHtml;
    }

    public static function getFooter() {
        return self::$footerHtml;
    }

    public static function setBreadcrumb(array $bread) {
        $breadcrumb = "";

        if($bread[0] != "invisible") {

            for($k = count($bread) - 1; $k >= 0; $k--) {
                if($bread[$k]['link'] != "#") {
                    self::$mobile_breadcrumb = $bread[$k]['link'];
                    break;
                }
            }

            for($i = 0; $i < count($bread); $i++) {
                $breadcrumb .= "<a href='" . $bread[$i]['link'] . "'>";
                    $breadcrumb .=  $bread[$i]['text'];
                    if($i + 1 < count($bread)) {
                        $breadcrumb .= " › ";
                    }
                $breadcrumb .= "</a>";
            }

            self::$breadcrumb = $breadcrumb;
        } else {
            self::$breadcrumb = "invisible";
        }
    }

    public static function getBreadcrumb($mode = "") {
        if($mode == "mobile") {
            return self::$mobile_breadcrumb;
        } else {
            return self::$breadcrumb;
        }
    }

    public static function setRouteParams(array $params): void {
        self::$routeParams = $params;
    }

    public static function getRouteParam(string $key, mixed $default = null): mixed {
        return self::$routeParams[$key] ?? $default;
    }

}