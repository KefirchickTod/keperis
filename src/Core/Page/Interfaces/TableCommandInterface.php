<?php


namespace src\Core\Page\Interfaces;


use src\Core\Table\eeTable;

interface TableCommandInterface
{

    public function execute() : eeTable;
}