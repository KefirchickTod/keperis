<?php
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$defins = new \src\Collection($dotenv->load());
$env = $defins->map(function ($value, $key) {
    if (!defined($key)) {

        define($key, $value);
    }
    return $value;
});
$app = new src\BcClub(
    require_once "config.php"
);
/**
 * @param null $app
 * @return \src\BcClub
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

require_once "../src/function.php";

require_once "../app/config.php";




require_once "route.php";



return app();