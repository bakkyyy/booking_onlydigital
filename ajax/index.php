<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use OnlyDigital\Mvc\Controller\Prototype;

const STOP_STATISTICS = true;
const NO_AGENT_CHECK = true;
const NO_KEEP_STATISTIC = true;
const DisableEventsCheck = true;
const PUBLIC_AJAX_MODE = true;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$request = Application::getInstance()->getContext()->getRequest();

try {
    if (!Loader::includeModule('only.digital')) {
        throw new RuntimeException('Can\'t include module "only.digital".');
    }
    $name = htmlspecialchars($request->getQuery("controller"));
    $action = htmlspecialchars($request->getQuery("action"));

    $controller = Prototype::factory($name);
    $controller->doAction($action);
} catch (\Throwable $e) {
    // Лог
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
