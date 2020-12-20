<?php


namespace App\Provides;


class TestMask extends Mask
{

    protected $mask = [
        'test' => [
            'user' => [
                'get' => ['name'],
                'class' => \App\Provides\Structures\TestedIncludes::class
            ],
        ]
    ];
    protected $title = [
        'test' =>
            [
                'id' => [
                    'text' => 'Id',
                    'sort' => true
                ],
                'name' =>
                    [
                        'text' => 'Name',
                        'sort' => true
                    ]
            ]
    ];
}