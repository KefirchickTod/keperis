<?php

app()->get('/', function (){
    $test = \sc\Core\Database\DatabaseFactory::table('bc_user');
    var_dump($test);exit;
})->name('test.controller');