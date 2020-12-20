<?php


namespace src\Middleware;


use src\Http\Request;
use src\Interfaces\MiddlewareInterface;
use src\Interfaces\RequestHandlerInterface;
use src\Interfaces\ResponseInterface;
use src\Middleware\Middlewares\AuthonficationMiddleware;
use src\Traits\CallableResolverAwareTrait;

abstract class MiddlewareHandler implements RequestHandlerInterface
{
    use CallableResolverAwareTrait;

    protected $stack;

    protected $middlewareLock = false;

    protected $fallbackHandler;

    public function __construct(RequestHandlerInterface $fallbackHandler)
    {
        $this->seedMiddlewareStack();
        $this->fallbackHandler = $fallbackHandler;
    }


    private function seedMiddlewareStack($kernel = null)
    {
        if (!is_null($this->stack)) {
            throw new \RuntimeException('MiddlewareStack can only be seeded once.');
        }
        if ($kernel == null) {
            $kernel = $this;
        }
        $this->stack = [];
        $this->stack[] = $kernel;

    }

    public function addMiddleware(MiddlewareInterface $middleware)
    {
        if ($this->middlewareLock) {
            throw new \RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        $this->stack[] = $middleware;

    }


    /**
     * @param $request
     * @param RequestHandlerInterface $requestHandler
     * @return ResponseInterface
     */
    public function handle($request, RequestHandlerInterface $requestHandler = null)
    {
        if (is_null($this->stack) || 0 === count($this->stack)) {
            return $this->fallbackHandler->handle($request, $this);
        }
        $start = array_shift($this->stack);
        $this->middlewareLock = true;


      //  var_dump($start, count($this->stack));
        $resp = $start->process($request, $this);
//        if($start instanceof AuthonficationMiddleware){
//            debug($resp, $start);
//        }
        $this->middlewareLock = false;


        return $resp;
    }
}