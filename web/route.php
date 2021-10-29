<?php

use src\Core\Database\DatabaseAdapter;

app()->get('/login', function (){
    $users = db()->selectSql('user',"*");
    foreach ($users as $user){
        echo "User({$user['user_id']}) : {$user['user_first_name']} {$user['user_secon_name']} <br>";
    }

    exit;
    //return "<h1>Plead login</h1>";
})->name('test.controller');