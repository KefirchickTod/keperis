<?php


namespace sc\Core\Database\Interfaces;


interface DatabaseFactoryInterface
{
    /**
     * @param string $table
     * @return DatabaseBuilderInterface
     */
    public static function table(string $table);
    public static function line(string $query);
}