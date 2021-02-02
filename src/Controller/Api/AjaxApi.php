<?php


namespace src\Controller\Api;


use src\Interfaces\ApiInterfaces;

class AjaxApi implements ApiInterfaces
{

    /**
     * @var array|mixed
     * @template [
     *      $apiclontroller::class => ['method1',...,..., 'methodN'],
     * ];
     */
    private $apiStructure;

    /**
     * @var ApiController|ErrorApi
     */
    private $class;

    private $method;

    /**
     * @var bool
     */
    private $errorMethod;

    public function __construct($get, $apiStructure = [])
    {
        $this->method = $get['method'] ?? null;
        $this->errorMethod = false;
        $this->apiStructure =$apiStructure;
    }


    /**
     * @inheritDoc
     */
    public function getController()
    {
        if(!$this->apiStructure){
            $this->errorMethod = true;
            return new ErrorApi('Api structure is empty');
        }

        foreach ($this->apiStructure as $class => $value){
            if(in_array($this->method, $value) || in_array($this->method."Api", $value)){
                $class =  new $class;

                if(!method_exists($class, $this->method)){
                    $this->method = $this->method . "Api";
                }
                return $class;
            }
        }
        $this->errorMethod = true;
        return new ErrorApi("Cant find class by method");
    }

    public function getMethod()
    {
        return $this->errorMethod === true ? 'failed' : $this->method;
    }


}