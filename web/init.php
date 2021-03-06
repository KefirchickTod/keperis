<?php
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$defins = new \src\Collection($dotenv->load());
$env = $defins->map(function ($value, $key) {
    if (!defined($key)) {

        define($key, $value);
    }
    return $value;
});
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

require_once ROOT_PATH . "/src/function.php";

if (file_exists(ROOT_PATH . "/app/config.php")) {
    require_once ROOT_PATH . "/app/config.php";
}


require_once "route.php";
require_once "middleware.php";


return app();