<?php


namespace src\Core\Database;


use ArrayIterator;
use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseStatementInterface;
use src\Interfaces\CollectionInterface;

class DatabaseStatement implements DatabaseStatementInterface
{
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

        $this->query = new \stdClass();
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function copy(): CollectionInterface
    {
        // TODO: Implement copy() method.
    }

    public function has($key)
    {
        // TODO: Implement has() method.
    }

    public function toArray(): ArrayIterator
    {
        // TODO: Implement toArray() method.
    }

    public function set($key, $value)
    {
        // TODO: Implement set() method.
    }

    public function get($key, $default = [])
    {
        // TODO: Implement get() method.
    }

    /**
     * @inheritDoc
     */
    public function hasMany(array $key)
    {
        // TODO: Implement hasMany() method.
    }

    public function fetch($mode = \PDO::FETCH_ASSOC)
    {
        // TODO: Implement fetch() method.
    }

    public function indexColumn(string $key)
    {
        // TODO: Implement inexColumn() method.
    }

    public function getSql(){
        if($this->query->where){
            $this->query->base .= " WHERE ".$this->query->where;
        }
        return $this->query->base;
    }

    public function toBase(): array
    {

        if($this->query->type === DatabaseBuilder::MODE_SELECT){
            $data = $this->connection->select($this->getSql(), \PDO::FETCH_OBJ);
        }else{
            $data = $this->connection->line($this->getSql());
        }

        return  $data;

    }
}