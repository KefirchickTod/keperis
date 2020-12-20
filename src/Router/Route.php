<?php


namespace src\Router;


use src\Controller\Controller;
use src\Core\Middleware\NotFoundHandler;
use src\Http\Body;
use src\Http\Request;
use src\Http\RequestResponse;
use src\Http\Response;
use src\Interfaces\MiddlewareInterface;
use src\Interfaces\ResponseInterface;
use src\MiddlewareHandeler;
use src\Resource;
use Exception;
use FastRoute\RouteParser\Std;
use LogicException;
use src\View\View;

class Route
{

    /**
     * @var array|string[]
     * Array of allow method
     */
    private $methods = [];
    /**
     * @var string
     * pattern uri (user/list etc.)
     */
    private $pattern = '';
    /**
     * @var Controller|\Closure
     * Controller object of callable for callback
     */
    private $controller;

    private $indication;

    /**
     * @var string|null
     */
    private $func;
    /**
     * @var array
     * arguments from StdParse
     */
    private $arguments = [];

    function __construct(array $methods, $pattern, $controller, $indication, $func)
    {
        $this->methods = $methods;
        $this->pattern = $pattern;
        $this->controller = $controller;
        $this->indication = $indication;
        $this->func = $func;


    }




    public function getPath(array $params = [])
    {
        $routeParser = new Std();
        // (Maybe store the parsed form directly)
        $routes = $routeParser->parse($this->pattern);

        // One route pattern can correspond to multiple routes if it has optional parts
        foreach ($routes as $route) {
            $url = '';
            $paramIdx = 0;

            foreach ($route as $part) {
                // Fixed segment in the route
                if (is_string($part)) {
                    $url .= $part;
                    continue;
                }

                // Placeholder in the route
                if ($paramIdx === count($params)) {
                    throw new LogicException('Not enough parameters given');
                }

                $url .= $params[$paramIdx++] ?? $params[$part[0]];

            }

            // If number of params in route matches with number of params given, use that route.
            // Otherwise try to find a route that has more params
            if ($paramIdx === count($params)) {
                return $url;
            }
        }

        //debug($params, $routes);
        throw new LogicException('Too many parameters given '.$url);
    }

    function getMethods()
    {
        return $this->methods;
    }


    function getPattern()
    {
        return $this->pattern;
    }

    function withIndication($name){
        $clone = clone $this;
        $clone->indication = $name;
        return $clone;
    }

    function getIndication()
    {
        return $this->indication;
    }

    function getController()
    {
        return $this->controller;
    }

    /**
     * Retrieve route arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Replace route arguments
     *
     * @param array $arguments
     *
     * @return Route
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }

    /**
     * Retrieve a specific route argument
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getArgument($name, $default = null)
    {
        if (array_key_exists($name, $this->arguments)) {
            return $this->arguments[$name];
        }
        return $default;
    }

    public function prepare(Request $request, array $arguments)
    {
        // Add the arguments
        foreach ($arguments as $k => $v) {
            $this->setArgument($k, $v);
        }
    }

    public function setArgument($name, $value)
    {
        $this->arguments[$name] = $value;
        return $this;
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param $controller Controller| \Closure
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(Request $request, Response $response, $controller, $routerinfo = [])
    {
        $routerinfo = $routerinfo ?: $request->getAttribute('routeInfo');
        $this->controller = $controller instanceof Controller || $controller instanceof \Closure ? $controller : new $controller;
        $handler = new RequestResponse();


        try {
            $output = $handler($controller, $this->getFunc(), $request, $response, $routerinfo);

            //$output = ($output instanceof View && $output->isResource()) ? $output->getResource() : $output;

            if ($output instanceof View) {
                ob_start();
                echo $output->render();
                $output = ob_get_clean() . PHP_EOL;
                ob_clean();
            } elseif ($output instanceof ResponseInterface) {
                $response = $output;
            }
        } catch (Exception $e) {
            ob_end_clean();
            error_log($e->getMessage());
            throw  $e;
        }

//        $response = $this->middleware->handle($request);

        if (!empty($output) && is_string($output)) {
            if ($response->getBody()->isWritable()) {
                $body = new Body(fopen('php://temp', 'r+'));

                $body->write(join('', [
                    $output,
                    $response->getBody(),
                ]));

                return $response->withBody($body);
            } else {

                $response->getBody()->write($output);
                return $response;
            }

        }

        return $response;
    }

    public function withPattern($pattern){
        $clone = clone $this;
        $clone->pattern = $pattern;
        return $clone;
    }

    public function getFunc()
    {
        return $this->func;
    }

    public function setFunc($name)
    {
        $this->func = $name;
    }
}