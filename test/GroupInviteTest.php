<?php

namespace Controller;

use App\Controller\Controller;
use App\Controller\GroupInvite;
use PHPUnit\Framework\TestCase;
use src\Http\Request;
use src\Http\ServerData;
use src\Http\Uri;
use src\Structure\ProvideStructures;

class bcTest extends ProvideStructures
{
    protected $sqlSetting =
        [
            'table' => 'bc_user',
            'prefix' => 'bc_user_',
            'id' => 'bc_user_id AS id',
            'userId' =>
                [
                    'select' => 'bc_user_id',
                    'as' => 'userId',
                ],
            "fullName" =>
                [
                    'select' => "CONCAT(COALESCE(bc_user_secondname_uk, ' '),' ', COALESCE(bc_user_name_uk, ' '))",
                    'as' => 'fullName',
                    'type' => 'string',


                ],


        ];
}

class InviteController extends Controller
{
    use GroupInvite;

    protected $structure = [
        'get' => [
            'fullName',
        ],
        'class' => bcTest::class,
    ];
}

class GroupInviteTest extends TestCase
{

    public function testInvite()
    {
        $request = Request::creatFromServerData(new ServerData($_SERVER));
        $request = $request->withUri(Uri::createFromString('?search=test&filter={}'));


        $controller = new InviteController(container());


        $response = $controller->invite($request, container()->response);
//todo


    }
}
