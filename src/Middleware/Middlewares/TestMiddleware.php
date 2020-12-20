<?php


namespace src\Middleware\Middlewares;


use src\Interfaces\MiddlewareInterface;
use src\Interfaces\RequestHandlerInterface;

class TestMiddleware implements MiddlewareInterface
{

    public function process($request, RequestHandlerInterface $handler)
    {

        return $handler->handle($request);
    }
}