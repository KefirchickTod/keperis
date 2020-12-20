<?php


namespace src\Interfaces\Structure;


interface bcProvideRegisterInterface
{
    public static function set($key, $value);

    public static function get($key);

    public static function removeData($key);
}