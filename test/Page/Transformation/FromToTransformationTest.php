<?php

namespace App\Middleware\PageCreator;

use PHPUnit\Framework\TestCase;
use src\Page\DataTransformation;
use src\Structure\ProvideStructures;

class bcTest extends ProvideStructures
{
    protected $sqlSetting = [
        'table'       => 'none',
        'fullName'    => [
            'select' => 'false',
            'type'   => 'string',
        ],
        'secondName'  => [
            'select' => 'select2',
            'type'   => 'string',
        ],
        'selectemail' => [
            'select' => 'select-email',
            'type'   => 'email',
            'templates' => "GROUP_CONCAT(%_select_% SEPARATOR ' | ')",
        ],
    ];
}

class FromToTransformationTest extends TestCase
{

    public function test__invoke()
    {
        $structure = [
            'get'   => [
                'fullName',

            ],
            'class' => bcTest::class,
        ];
        $transformation = new DataTransformation($structure);
        $transformation->addFilter(FromToTransformation::class);
        $data = $transformation->callFilter([
            'from'   => '2021-12-12',
            'to'     => null,
            'parent' => 'fullName',
        ]);


        $exepted = "false >= '2021/12/12'";

        $this->assertEquals($exepted, $data['setting']['where']);
    }
}
