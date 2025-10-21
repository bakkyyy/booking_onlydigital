<?php

use Bitrix\Main\Loader;

try {
    Loader::registerAutoLoadClasses('only.digital', [
        '\OnlyDigital\Mvc\Controller\Drives' => 'lib/mvc/controller/drives.php',
    ]);
} catch (\Throwable $e) {
    // Алерт
}
