<?php


namespace src\Models;

use ArrayIterator;
use Exception;
use src\Interfaces\CollectionInterface;

abstract class ModelCollection implements CollectionInterface
{

    protected $data;

    public function hasMany(array $keys){
        foreach ($keys as $key){
            if($this->has($key)){
                return true;
            }
        }
        return false;
    }

    public function set($key, $value)
    {
        if ($key === null) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }

    public function remove()
    {
        $argc = func_get_args();
        foreach ($argc as $key) {
            if ($this->has($key)) {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return (bool)array_key_exists($key, $this->data);
    }

    public function all()
    {
        return $this->data;
    }
    /**
     * @return void
     */
    public function clear()
    {
        $this->data = [];
    }

    public function count()
    {
        return sizeof($this->data);
    }

    public function toArray(): ArrayIterator
    {
        $iterator = $this->getIterator();
        if ($iterator instanceof ArrayIterator) {
            return $iterator;
        }
        return new ArrayIterator($this->data);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }

    public function __get($name)
    {
        if ($this->has($name)) {
            return $this->data[$name];
        }
        return null;
    }

    public function map(callable $callback)
    {

        $array_values = array_values($this->data);
        $array_keys = array_keys($this->data);

        $array = array_map($callback, $array_values, $array_keys);
        return array_combine($array_keys, $array);

    }


    public function first()
    {
        $key = key($this->data);
        return $this->get($key);
    }


    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            try {
                $result = $this->data[$key] ?? null;
                if (is_null($result)) {

                    throw new Exception();
                }
                return $this->data[$key];
            } catch (Exception $exception) {

                return $this->data[strtolower($key)];
            }
        }
        return $default;
    }
}