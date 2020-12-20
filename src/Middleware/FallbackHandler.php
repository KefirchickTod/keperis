<?php


namespace src\Middleware;


use src\Interfaces\MiddlewareInterface;
use src\Interfaces\RequestHandlerInterface;

class FallbackHandler implements RequestHandlerInterface
{

    /**
     * @inheritDoc
     */
    public function handle($request, RequestHandlerInterface $requestHandler = null)
    {
        return container()->get('response');
    }
}