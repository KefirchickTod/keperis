<?php


namespace src\Core\Database\Interfaces;


interface DatabaseAdapterInterface
{

    /**
     * @return \PDO
     * Return connection object Pdo
     */
    public function getPdo() : \PDO;


    public function line(string $query);

}