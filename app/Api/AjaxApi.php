<?php


namespace App\Api;


class AjaxApi
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

    public function __construct($apiStructure = [], $method = null){
        $this->apiStructure = $apiStructure;
        if($method){
            $this->class = $this->getClassByMethod($method);
        }
    }


    /**
     * @param string $method
     * @return ApiController
     */
    public function getClassByMethod(string $method){
        if(!$this->apiStructure){
            return new ErrorApi('Api structure is empty');
        }
        foreach ($this->apiStructure as $class => $value){
            if(in_array($method, $value) || in_array($method."Api", $value)){
                return new $class;
            }
        }
        return new ErrorApi("Cant find class by method");

    }


    public function get(){
        return $this->class;
    }
}