<?php


namespace sc\Core\Database;


use sc\Core\Database\Interfaces\DatabaseBuilderInterface;

class DatabaseBuilder implements DatabaseBuilderInterface
{

    /**
     * @var DatabaseAdapter
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $query = [];

    public function __construct(string $table, DatabaseAdapter $connection)
    {
        $this->table = $table;
        $this->connection = $connection;
        $table->query = [];
    }


    protected function valid(){
    }

    public function select($fields = ["*"])
    {

    }

    public function where($fields)
    {
        // TODO: Implement where() method.
    }

    public function limit($from, $to = null)
    {
        // TODO: Implement limit() method.
    }

    public function order($fields)
    {
        // TODO: Implement order() method.
    }

    public function join()
    {
        // TODO: Implement join() method.
    }

    public function delete($fields)
    {
        // TODO: Implement delete() method.
    }

    public function insert($fields)
    {
        // TODO: Implement insert() method.
    }
}