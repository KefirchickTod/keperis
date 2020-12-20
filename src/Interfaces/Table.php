<?php


namespace src\Interfaces;


interface Table extends PageInterface
{
    public function setting(array $setting = [], array $title = [], $filter = [], array $action = []);
}