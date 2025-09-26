<?php

const CACHE_DIR = __DIR__ . "/cache";
const CACHE_ENABLED = 1;
const JWT_KEY = 'IranAPITokenKey';
const JWT_ALG = 'HS256';

include __DIR__ . "/app/index.php";
include "vendor/autoload.php";

spl_autoload_register(function ($class) {
    $className = __DIR__ . "/$class" . ".php";
    if (file_exists($className) and is_readable($className)) {
        include $className;
    } else {
        die("$class not found");
    }
});
