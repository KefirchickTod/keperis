<?php
session_start();
define("APP_START", microtime(true));
define("ROOT_PATH", dirname(__DIR__));

//todo create new paginator
//todo create a dafault method for ajax in controller
//todo add callback to mask
//todo repository

//todo __invoke middlewhare
//todo update eEgnith


require_once "../vendor/autoload.php";

$app = require_once "../web/init.php";

$app->run();

