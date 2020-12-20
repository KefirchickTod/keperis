<?php


namespace src\Core;


class RowMiddlewhere
{

    public function handle($value)
    {
//        if(isset($value['mobile'])){
//            $value['mobile'] = $value['mobile'];
//        }
//        if(isset($value['email'])){
//            $value = $value['email'];
//        }
        return $value;
    }
}