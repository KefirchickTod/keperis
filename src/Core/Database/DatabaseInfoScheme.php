<?php


namespace src\Core\Database;


use src\Core\Database\Interfaces\DatabaseAdapterInterface;
use src\Core\Database\Interfaces\DatabaseInfoSchemeInterface;

/**
 * Class DatabaseInfoScheme
 * @package src\Core\Database
 * @version 0.1
 * Get info about table and columns
 */
class DatabaseInfoScheme implements DatabaseInfoSchemeInterface
{

    const TABLE_INFO_TEMPLATE = "SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{%_scheme_%}' AND TABLE_NAME = '{%_table_%}' ";
    const TABLE_COLUMN_INFO_TEMPLATE = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{%_table_%}'";
    protected $column;
    protected $data;
    private $table;
    private $schema;

    public function __construct(string $table, DatabaseAdapterInterface $connection)
    {
        $this->table = $table;
        $this->parse($connection);
    }

    private function parse(DatabaseAdapterInterface $connection)
    {
        $tableInfoQuery = preg_replace(["/{%_scheme_%}/", "/{%_table_%}/"], [env('DB_NAME'), $this->table],
            static::TABLE_INFO_TEMPLATE);
        $tableColumnInfoQuery = preg_replace("/{%_table_%}/", $this->table, static::TABLE_COLUMN_INFO_TEMPLATE);
        $column = $connection->select($tableColumnInfoQuery);
        $data = $connection->select($tableInfoQuery)[0] ?? null;
        if (!$data) {
            throw new \PDOException("Undefined info about table");
        }
        $this->data = $data;
        $this->schema = $this->data['table_schema'];
        $this->column = [];
        foreach ($column as $row) {
            $this->column[$row['column_name']] = $row;
        }
    }

    public function table(): string
    {
       return  $this->table;
    }

    /**
     * @inheritDoc
     */
    public function getTableSchema(): string
    {
        return $this->schema;
    }

    /**
     * @inheritDoc
     */
    public function getTableType(): string
    {
        return $this->data['table_type'];
    }

    /**
     * @inheritDoc
     */
    public function getEngine(): string
    {
        return $this->data['engine'];
    }

    /**
     * @inheritDoc
     */
    public function version(): int
    {
        return intval($this->data['version']);
    }

    /**
     * @inheritDoc
     */
    public function getRowFormat(): string
    {
        return $this->data['row_format'];
    }

    /**
     * @inheritDoc
     */
    public function getTableRows(): int
    {
        return intval($this->data['table_rows']);
    }

    /**
     * @inheritDoc
     */
    public function getDataLength(): int
    {
        return intval($this->data['avg_row_length']);
    }

    /**
     * @inheritDoc
     */
    public function getCreateTime(): \DateTime
    {
        return date_create($this->data['create_time']);
    }

    /**
     * @inheritDoc
     */
    public function getComment(): string
    {
        return $this->data['table_comment'];
    }

    public function getTableCollation(): string
    {

        return  $this->data['table_collation'];
    }

    /**
     * @inheritDoc
     */
    public function isColumns(array $keys): bool
    {
        foreach ($keys as $key){
            if(!array_key_exists($key, $this->column)){
                return  false;
            }
        }
        return true;
    }

    public function getColumnDataTypeCallback(string $column) : string {

        $type = $this->getColumnsDataType($column);
        switch ($type){

            case 'mediumtext':
            case 'longtext':
            case 'text':
            case 'char' :
            case 'datetime' :
            case 'varchar' :
                $callback = 'strval';
                break;

            case 'tinyint':
            case 'bigint':
            case 'int':
            case 'integer':
                $callback = 'intval';
                break;
            case 'float':
            case 'double':
                $callback = 'floatval';
                break;
            case 'json':
                $callback = 'json_encode';
                break;
            default:
                $callback = null;
                break;
        }

        return $callback;
    }
    /**
     * @inheritDoc
     */
    public function getColumnsDataType(string $column): string
    {

        $type = $this->getFromColumn($column, 'data_type', 'varchar');
        return  $type;
    }

    /**
     * @inheritDoc
     */
    public function getColumnMaxLength(string $column): int
    {
        $lenth = $this->getFromColumn($column, 'character_maximum_length');
        return  $lenth;
    }

    /**
     * @inheritDoc
     */
    public function isNullable(string $column): bool
    {
        $nullable = $this->getFromColumn($column, 'is_nullable');
        if($nullable === 'no'){
            return  false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getColumnDefautl(string $column)
    {
        $default = $this->getFromColumn($column, 'column_default');
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function getColumnComment(string $column): string
    {
        $comment = $this->getFromColumn($column, 'column_comment');
        return  $column;
    }

    /**
     * @inheritDoc
     */
    public function getColumnExtra(string $column)
    {
        $extra = $this->getFromColumn($column, 'extra');
        return $extra;
    }

    /**
     * @param string $column
     * @param string $key
     * @param null $default
     * @return string|null
     * Get info from array with lover register
     */
    private function getFromColumn(string $column, string $key, $default = null)
    {
        if (!$this->isColumn($column)) {
            throw new \RuntimeException("Undefined column $column");
        }
        $value = $this->column[$column];
        if (!array_key_exists($key, $value)) {
            return $default;
        }

        return strtolower($value[$key]);
    }

    /**
     * @inheritDoc
     */
    public function isColumn(string $key): bool
    {
        if(!$this->column){
            throw new \RuntimeException("Undefined array column");
        }
        return array_key_exists($key, $this->column);
    }
}