<?php


namespace src\Middleware\RequestHandler;


use src\Http\Response;
use src\Interfaces\Handler\RequestHandlerInterface;
use src\Interfaces\ResponseInterface;
use src\Middleware\RequestHandler;

class TestHandler extends RequestHandler
{

    public function handle($request, Response $response, RequestHandlerInterface $requestHandler = null): ?ResponseInterface
    {
        return  $response;
    }
}