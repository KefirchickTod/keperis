<?php


namespace src\Core\Database\Interfaces;


use src\Interfaces\CollectionInterface;

interface DatabaseStatementInterface extends CollectionInterface
{

    public function fetch($mode = \PDO::FETCH_ASSOC);

    public function indexColumn(string $key);

    public function toBase() : array ;
}