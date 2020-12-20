<?php


namespace src\Middleware;


use src\Interfaces\MiddlewareInterface;
use src\Interfaces\RequestHandlerInterface;

class Middleware extends MiddlewareHandler implements MiddlewareInterface
{


    public function process($request, RequestHandlerInterface $handler)
    {

        return $handler->handle($request);
    }
}