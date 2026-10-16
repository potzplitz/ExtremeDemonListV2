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