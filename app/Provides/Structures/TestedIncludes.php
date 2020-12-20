<?php


namespace App\Provides\Structures;


use src\Structure\ProvideStructures;

class TestedIncludes extends ProvideStructures
{

    protected $factoryName = 'test';
    protected $name = 'test';
    protected $sqlSetting = [
        'table' => 'user',
        'id' => 'id',
        'name' => [
            'select' => 'user_name',
            'as' => 'name'
        ],
    ];
}