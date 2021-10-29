<?php


namespace src\Core\Database;


use ArrayIterator;
use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseInfoSchemeInterface;
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
    protected $queryString = '';

    /**
     * @var DatabaseInfoSchemeInterface
     */
    protected $infoScheme;

    private $data = [];

    public function __construct(string $queryString, DatabaseInfoSchemeInterface $infoScheme,  DatabaseAdapterInterface $connection)
    {

        $this->queryString = $queryString;
        $this->infoScheme = $infoScheme;

        $this->connection = $connection;

        $this->data = $this->fetch();

    }

    public function clear()
    {
        $this->data = [];
    }

    public function copy(): CollectionInterface
    {
        // TODO: Implement copy() method.
    }

    public function has($key)
    {
        return $this->infoScheme->isColumn($key);
    }

    public function toArray(): ArrayIterator
    {
        // TODO: Implement toArray() method.
    }

    public function column($value, $index = null){
        $result = array_column($this->data, $value, $index);
        return $result;
    }

    /**
     * @return DatabaseInfoScheme|string
     */
    public function info()
    {
        return $this->infoScheme;
    }

    public function set($key, $value)
    {
        $this->data = array_map(function ($row) use ($key, $value){
            if(array_key_exists($key, $row)){
                $row[$key] = $value;
            }
            return $row;
        }, $this->data);
    }

    public function get($key, $default = [])
    {
        // TODO: Implement get() method.
    }

    public function groupValueById(string $value, string $id){
        $result = [];
        foreach ($this->data as $row){
            $row = (array)$row;

            if(array_key_exists($id, $row) && $row[$id]){
                $result[$row[$id]][] = $row[$value];
            }


            //var_dump($row);

        }
        return $result;
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
        if (DatabaseBuilder::MODE_SELECT === 1) {
            $data = $this->connection->select($this->queryString, \PDO::FETCH_OBJ);
        } else {
            $data = $this->connection->line($this->queryString)->fetchAll(\PDO::FETCH_ASSOC);
        }


        return $data;
    }

    public function indexColumn(string $key)
    {
        // TODO: Implement inexColumn() method.
    }


    public function toBase(): array
    {


        if (DatabaseBuilder::MODE_SELECT === 1) {
            $data = $this->connection->select($this->queryString, \PDO::FETCH_OBJ);
        } else {
            $data = $this->connection->line($this->queryString)->fetchAll(\PDO::FETCH_ASSOC);
        }


        return $data;

    }

}