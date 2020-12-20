<?php


namespace src\Interfaces;


interface VectorInterface
{
    public function push($value);
    public function find($key);
    public function last();
    public function set($key, $value);
    public function sort(callable $callable);
    public function remove($key);
    public function toArray() : array;
    public function keys() : array;
    public function update($key, $value);
}