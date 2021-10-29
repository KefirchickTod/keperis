<?php


namespace src\Core\Database\Interfaces;



interface DatabaseInfoSchemeInterface
{


    /**
     * @return string
     * Get name sql info scheme for table
     */
    public function getTableSchema() : string;

    /**
     * @return string
     * Get table type by default BASE TABLE
     */
    public function getTableType() : string;

    /**
     * @return string
     * Get subproccecor of database
     */
    public function getEngine() : string ;

    /**
     * @return int
     * Version
     */
    public function version() : int;

    /**
     * @return string
     * Get name of row format
     */
    public function getRowFormat() : string ;

    /**
     * @return int
     * Get total size of rows
     */
    public function getTableRows() : int;

    /**
     * @return int
     * Get current size (length) of data
     */
    public function getDataLength() : int;

    /**
     * @return \DateTime
     * Get date of created
     */
    public function getCreateTime() : \DateTime;

    /**
     * @return string
     * Get comment of table
     */
    public function getComment() : string ;

    public function getTableCollation() : string ;

    /**
     * @param string $key
     * @return bool
     * Check if isset column in {n} table
     */
    public function isColumn(string $key) : bool;

    /**
     * @param array $keys
     * @return bool
     * Check of isset array column in {n} table
     */
    public function isColumns(array $keys) : bool ;

    /**
     * @param string $column
     * @return string
     * Get type of column
     */
    public function getColumnsDataType(string $column) : string;

    public function getColumnDataTypeCallback(string $column) : string ;
    /**
     * @param string $column
     * @return int
     * Get max length of column
     */
    public function getColumnMaxLength(string $column) : int;

    /**
     * @param string $column
     * @return bool
     * Check of allow set null for column
     */
    public function isNullable(string $column) : bool ;

    /**
     * @param string $column
     * @return mixed
     * Check if isset default value for column
     */
    public function getColumnDefautl(string $column);

    /**
     * @param string $column
     * @return string
     * Get comment
     */
    public function getColumnComment(string $column) : string ;

    /**
     * @param string $column
     * @return mixed
     * Get auto_incert or other;
     */
    public function getColumnExtra(string $column);


    public function table() : string ;



}