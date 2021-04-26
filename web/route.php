<?php

use src\Core\Database\DatabaseAdapter;

app()->get('/', function (){
    $test = new \src\Core\Database\DatabaseInfoScheme('bc_user', DatabaseAdapter::createDateBaseConnection(container()->env));
    var_dump($test);exit;
})->name('test.controller');