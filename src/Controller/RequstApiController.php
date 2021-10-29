<?php


namespace src\Controller;


use src\Container;
use src\Controller\Api\ApiController;
use src\Controller\Api\ErrorApi;
use src\Http\Request;
use src\Http\Response;
use src\Controller\Api\ApiFactory;

class RequstApiController extends Controller
{

    protected $apiFactory;

    public function __construct(Container $container)
    {
        parent::__construct($container);


        $this->apiFactory = new \App\Api\ApiFactory($container->request->getUri()->getParseQuery(),
            $container->request);
    }

    public function outer(Request $request, Response $response)
    {
        error_reporting(0);
        ini_set('display_errors', 0);

        $api = $this->apiFactory->api();

        /**
         * @var $controller ApiController
         */
        $controller = $api->getController();
        if ($controller instanceof ErrorApi) {
            return $response->withJson([$controller->failed()]);
        }
        $response = $controller->run($request, $response, $api->getMethod());

        return $response;
    }

    public function insider(Request $request, Response $response)
    {
        $api = \src\Api\ApiFactory::ajax($request);


        return $api->run();

//        $api = $this->apiFactory->getApi();
//
//        $controller = $api->getController();
//
//        $response = $controller->run($request, $response, $api->getMethod());
//
//        return $response;
    }
}