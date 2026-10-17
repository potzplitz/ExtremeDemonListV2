<?php

function dd($val) {
    var_dump($val);
    die;
}

function h($val) {
    return htmlspecialchars($val);
}

function echo_json($arr) {
    header("Content-Type: application/json");
    echo json_encode($arr, JSON_PRETTY_PRINT);
}

function timer_start($name) {
    global $__timers;
    $__timers[$name] = microtime(true);
}

function timer_end($name) {
    global $__timers;
    if (isset($__timers[$name])) {
        $elapsed = microtime(true) - $__timers[$name];
        $elapsed = number_format($elapsed, 2);
        echo "<!-- Timer [$name]: " . $elapsed . "s -->";
        return $elapsed;
    }
}