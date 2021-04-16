<?php


namespace sc\Core\Database\Interfaces;


interface DatabaseAdapterInterface
{

    /**
     * @return \PDO
     * Return connection object Pdo
     */
    public function getPdo() : \PDO;


}