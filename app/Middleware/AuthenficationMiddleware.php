<?php


namespace App\Middleware;


use src\Http\Request;
use src\Interfaces\Handle\RequestHandlerInterface;
use src\Interfaces\ResponseInterface;
use src\Middleware\RequestHandler;

class AuthenficationMiddleware extends RequestHandler
{

    public function handle($request, $response, RequestHandlerInterface $requestHandler = null): ?ResponseInterface
    {




        if($request->getRouteName() === 'api'){
            return parent::handle($request, $response, $requestHandler);
        }

        if(!isLogin() && $request->getRouteName() !== 'login'){
            return $response->withRedirect('/login');
        }
        return parent::handle($request, $response, $requestHandler);
    }
}