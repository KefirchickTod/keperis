<?php
session_start();

define("ROOT_PATH", dirname(__DIR__));

Dotenv\Dotenv::createImmutable(ROOT_PATH)->load();


$app = new src\App(
    require_once "config.php"
);

/**
 * @param null $app
 * @return \src\App
 */
function app($app = null)
{
    static $singleton;
    if (!$singleton) {
        $singleton = $app;
    }
    return $singleton;
}

app($app);

require_once ROOT_PATH . "/src/helper.php";

//require_once ROOT_PATH . "/app/config.php";


require_once "route.php";



return app();
