<?php

define("APP_START", microtime(true));


//todo create new paginator
//todo create a dafault method for ajax in controller
//todo add callback to mask
//todo repository




require_once"../vendor/autoload.php";


$app = app();

$app->run();

