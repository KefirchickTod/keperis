<?php


namespace src\Interfaces;


use src\Structure\Structure;

interface PageInterface
{
    public function render(): string;

    function setData(Structure $structure, array $dataArray);
}