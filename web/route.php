<?php

app()->get('/', function () {
    var_dump("Hello world");
})->name('test.controller');