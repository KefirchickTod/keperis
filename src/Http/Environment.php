<?php


namespace src\Http;


use src\Collection;

class Environment extends Collection
{

    public static function mock($userData){
        return new static($userData);
    }
}