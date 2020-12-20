<?php


namespace src\Interfaces\Structure;


interface provideStructureInterface
{
    public function setStructure(array $setting);

    public function outPutStructure($column);

}