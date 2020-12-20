<?php


namespace src\Interfaces\Structure;


interface ProvideCreateInterface
{
    public function getRow($page = false);

    public function getResult();
}