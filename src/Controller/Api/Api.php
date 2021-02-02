<?php


namespace src\Controller\Api;


use src\Controller\Api\Controller\UserApi;
use src\Http\Request;
use src\Interfaces\ApiInterfaces;

class Api implements ApiInterfaces
{

    private $class;
    private $method;

    private $post;
    /**
     * @var bool
     */
    private $errorMethod;
    /**
     * @var ErrorApi
     */
    private $error;

    public function __construct($get)
    {
        $this->class = $get['class'] ?? null;
        $this->method = $get['method'] ?? null;

        $this->errorMethod = false;
        $this->error = new ErrorApi();
    }

    public function error($msg = ''){
        $this->errorMethod = true;
        return $this->error->withMassage($msg);
    }
    protected function getNameSpace(){
        return __NAMESPACE__;
    }
    /**
     * @return ApiController
     */
    public function getController()
    {
        if (!$this->class || !$this->method) {
            return $this->error("Get empty parram (Class = {$this->class}, Method = {$this->method})");
        }


        $class = $this->getNameSpace() . "\Controller\\" . $this->class;


        if (class_exists($class)) {
            $class = new $class;
        }
        $class = $class . "Api";
        if (class_exists($class)) {
            $class = new $class;
        }

        if (!($class instanceof ApiController)) {
            return $this->error("Cant find class $class");
        }

        if (method_exists($class, $this->method) || method_exists($class, $this->method . "Api")) {
            $this->method = method_exists($class, $this->method . "Api") ? $this->method . 'Api' : $this->method;

            return $class;
        } else {
            return $this->error("Cant find method $this->method");
        }
        //return $error->withMassage("Cant find class");

    }

    public function getMethod()
    {
        return $this->errorMethod === true ? 'failed' : $this->method;
    }
}