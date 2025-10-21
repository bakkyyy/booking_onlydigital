<?php

use Bitrix\Main\Loader;

Loader::includeModule('only.digital');

spl_autoload_register(function ($class) {
    $prefix = 'OnlyDigital\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = strtolower(substr($class, strlen($prefix)));
    $file = $_SERVER['DOCUMENT_ROOT'] . '/local/modules/only.digital/lib/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
