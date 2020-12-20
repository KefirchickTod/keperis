<?php


namespace src\Core\Filtration;


interface FilterInterface
{
    public function setDataStructure(&$dataStructure);

    public function parse();
}