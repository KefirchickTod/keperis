<?php


namespace src\Interfaces\Handler;


use src\Http\Request;
use src\Http\Response;
use src\Interfaces\ResponseInterface;

/**
 * Handles a server request and produces a response.
 *
 * An HTTP request handler process an HTTP request in order to produce an
 * HTTP response.
 */
interface RequestHandlerInterface
{
    /**
     * Handles a request and produces a response.
     *
     * May call other collaborating code to generate the response.
     */
    public function handle(Request $request, ResponseInterface $response ,RequestHandlerInterface $requestHandler = null): ?ResponseInterface;

    public function setNext(RequestHandlerInterface $handler): RequestHandlerInterface;
}