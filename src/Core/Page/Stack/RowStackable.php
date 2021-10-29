<?php


namespace src\Core\Page\Stack;






use src\MiddlewareProvideTableTrait;

class RowStackable
{

    use MiddlewareProvideTableTrait;

    public function __invoke($data)
    {

        return $data;
    }

    public function add($callable)
    {
        $this->addMiddleware($callable);
        return $this;
    }
}