<?php


namespace src\Core;


use src\Interfaces\VectorInterface;

class Vector implements VectorInterface
{

    private $vector = [];

    public function __construct($vector = [])
    {
        if ($vector instanceof VectorInterface) {
            $vector = $vector->toArray();
        }
        $this->vector = $vector ?: $this->vector;
    }

    public function push($value)
    {
        array_push($this->vector, $value);
    }

    public function last()
    {
        if ($this->vector) {
            return $this->vector[end($this->vector)];
        }
        return null;
    }

    public function set($key, $value)
    {
        try {
            if (!$this->find($key)) {
                $this->vector[$key] = $value;
                return $this;
            }
            throw new \RuntimeException("Vector with $key already is");
        } catch (\RuntimeException $exception) {
            error_log($exception->getMessage());
            var_dump($exception->getMessage());
        }

        return $this;

    }

    public function find($key)
    {
        return valid($this->vector, $key);
    }

    public function sort(callable $callable)
    {
        return uasort($this->vector, $callable);
    }

    public function remove($key)
    {
        if (valid($this->vector, $key)) {
            unset($this->vector[$key]);
        }
        return $this;
    }

    public function toArray(): array
    {
        return $this->vector;
    }

    public function __get($name)
    {
        return $this->find($name);
    }
    public function keys(): array
    {
       if($this->vector){
           return  array_keys($this->vector);
       }
       return [];
    }
    public function update($key, $value)
    {
        if($this->find($key)){
            $this->vector[$key] = $value;
        }
        return $this;
    }
}