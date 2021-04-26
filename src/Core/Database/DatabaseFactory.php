<?php


namespace src\Core\Database;




use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseFactoryInterface;

class DatabaseFactory implements DatabaseFactoryInterface
{


    private static $connection;

    public static function table(string $table)
    {


        $builder = new DatabaseBuilder($table, self::connect());

        return $builder;

    }

    public static function connect(){
        if(!self::$connection || !(self::$connection instanceof DatabaseAdapterInterface)){
            self::$connection =  DatabaseAdapter::createDateBaseConnection(container()->env);
        }
        return self::$connection;
    }

    public static function line(string $query)
    {
        // TODO: Implement line() method.
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }
    private function __construct()
    {
    }
}