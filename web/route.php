<?php

app()->get('/', \App\Controller\TestContoller::class, 'index')->name('test.controller');