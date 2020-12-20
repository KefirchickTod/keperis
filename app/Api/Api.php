<?php


namespace App\Api;


use App\Api\Controller\UserApi;
use src\Http\Request;

class Api
{

    private $class;
    private $method;

    private $post;

    public function __construct($get, $post)
    {
        $this->class = $get['class'] ?? null;
        $this->method = $get['method'] ?? null;

        $this->errorMethod = false;
    }

    /**
     * @return bool
     */
    public function isXhr(){
        /**
         * @var $request Request
         */
        $request = container()->get('request');
        return $request->isXhr();
    }
    /**
     * @return ApiController
     */
    public function getController()
    {
        $error = new ErrorApi();
        if (!$this->class || !$this->method) {
            return $error->withMassage("Get empty parram (Class = {$this->class}, Method = {$this->method})");
        }

        $class =__NAMESPACE__."\Controller\\".$this->class;




        if(class_exists($class)){
            $class = new $class;
        }
        $class = $class."Api";
        if(class_exists($class)){
            $class =  new $class;
        }

        if(!($class instanceof ApiController)){
            return $error->withMassage("Cant find class");
        }

        if (method_exists($class, $this->method) || method_exists($class, $this->method . "Api")) {
            $this->method = method_exists($class, $this->method . "Api") ? $this->method.'Api' : $this->method;

            return $class;
        }else{
            $this->errorMethod = true;

            return $error->withMassage("Cant find method $this->method");
        }
        //return $error->withMassage("Cant find class");

    }

    /**
     * @return ApiController
     */
    private function getXhrController($class, $method){
        if($class === __NAMESPACE__."\Controller\Api"){
            $ajaxApi = new AjaxApi(require_once API_STRUCTURE_DIR, $method);
        }
        return new ErrorApi("Undefined default class for ajax $class");
    }

    public function getMethod()
    {
        return $this->errorMethod === true ?  'failed' : $this->method;
    }
}