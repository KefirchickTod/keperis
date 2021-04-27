<?php


namespace src\Core\Database;


use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseBuilderInterface;
use src\Core\Database\Interfaces\DatabaseStatementInterface;

class DatabaseBuilder extends DatabaseStatement implements DatabaseBuilderInterface
{

    const MODE_SELECT = 1;

    const SHCHEMA = "SELECT DATA_TYPE as type, COLUMN_NAME as name  FROM information_schema.COLUMNS WHERE TABLE_NAME='{%_table_%}'";


    public function select($fields = ["*"])
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $field) {
            if ($this->table->isColumn($field)) {
                $this->query->select[] =  $field;
            } else {
                error_log("Undefined field $field");
            }
        }
        if($this->query->select){
            $this->query->base = "SELECT ".join(', ', $this->query->select) . " FROM ".$this->table->table();
            $this->query->type = self::MODE_SELECT;
        }
        return $this;
    }

    public function where($fields)
    {
        if ($this->query->type !== self::MODE_SELECT){
            throw new \RuntimeException("Cant add to mode");
        }
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        $where = [];
        foreach ($fields as $field) {
            $where[] = $field;
        }
        $this->query->where = join(" ", $where);

        return $this;
    }

    public function limit($from, $to = null)
    {
        $this->query['limit'] = [$from, $to];
        return $this;
    }

    public function order($fields, $mode = 'DESC')
    {
        if (!is_array($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $field) {
            $this->query['order'][] = $field;
        }
        return $this;
    }

    public function join(array $join)
    {
        $this->query['join'] = $join;
    }

    public function delete($fields)
    {

    }

    public function insert($fields)
    {
        // TODO: Implement insert() method.
    }


    public function execute(): DatabaseStatementInterface
    {
        // TODO: Implement execute() method.
    }
}