<?php


namespace src\Core\Database;


use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseBuilderInterface;

class DatabaseBuilder implements DatabaseBuilderInterface
{

    const SHCHEMA = "SELECT DATA_TYPE as type, COLUMN_NAME as name  FROM information_schema.COLUMNS WHERE TABLE_NAME='{%_table_%}'";
    /**
     * @var DatabaseAdapterInterface
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

    public function __construct(string $table, DatabaseAdapterInterface $connection)
    {
        $this->table = new DatabaseInfoScheme($table, $connection);
        $this->connection = $connection;

        $this->query = [];
    }


    protected function valid($fields, string $operator){
        foreach ($fields as $field){
            if($this->table->isColumn($field)){
                $this->query[$operator][] = $field;
            }
        }
    }

    public function select($fields = ["*"])
    {
        if(!is_array($fields)){
            $fields = [$fields];
        }
        $this->valid($fields, 'select');
        return $this;
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