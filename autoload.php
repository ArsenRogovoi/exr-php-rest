<?php

spl_autoload_register(function ($class) {
    $filePath = __DIR__ . '/src/' . $class . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});
