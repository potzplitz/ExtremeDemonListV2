<?php

function dd($val) {
    var_dump($val);
    die;
}

function h($val) {
    return htmlspecialchars($val);
}