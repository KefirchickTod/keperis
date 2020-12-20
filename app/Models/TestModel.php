<?php


namespace App\Models;


use App\Provides\TestMask;
use src\Model;

class TestModel extends Model
{

    public $bc_table = 'users';
    public $mask = TestMask::class;
}