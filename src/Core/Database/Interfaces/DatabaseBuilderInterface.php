<?php


namespace sc\Core\Database\Interfaces;


interface DatabaseBuilderInterface
{


    public function select($fields = ["*"]);
    public function where($fields);
    public function limit($from, $to = null);
    public function order($fields);
    public function join();
    public function delete($fields);
    public function insert($fields);

}