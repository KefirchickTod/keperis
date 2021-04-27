<?php

use src\Core\Database\DatabaseAdapter;

app()->get('/', function (){
    $test = \src\Core\Database\DatabaseFactory::table('bc_user')->select(['bc_user_id', 'bc_user_name_uk'])->where(['bc_user_id = 1'])->toBase();
    var_dump($test);exit;
})->name('test.controller');