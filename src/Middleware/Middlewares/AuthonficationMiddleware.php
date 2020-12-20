<?php


namespace src\Middleware\Middlewares;


use src\Http\Request;
use src\Http\Response;
use src\Interfaces\MiddlewareInterface;
use src\Interfaces\RequestHandlerInterface;

class AuthonficationMiddleware implements MiddlewareInterface
{

    /**
     * @inheritDoc
     */
    public function process(Request $request, RequestHandlerInterface $handler)
    {
        return $handler->handle($request);

    }
}