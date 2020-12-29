<?php


namespace src\Middleware\RequestHandler;


use src\Interfaces\Handle\RequestHandlerInterface;
use src\Interfaces\ResponseInterface;
use src\Middleware\RequestHandler;

class NotFoundHandler extends RequestHandler
{


    public function handle($request, $response, RequestHandlerInterface $requestHandler = null): ?ResponseInterface
    {
        return $response;
    }
}