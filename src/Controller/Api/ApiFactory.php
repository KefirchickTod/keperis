<?php


namespace src\Controller\Api;


use src\Http\Request;
use src\Interfaces\ApiInterfaces;

class ApiFactory
{

    /**
     * @var array
     */
    protected $get;

    /**
     * @var Request
     */
    private $request;
    public function __construct($get, Request $request = null)
    {
        $this->get = $get;
        $this->request = $request;
    }

    /**
     * @param Request|null $request
     * @return ApiInterfaces
     */
    public static function make(Request $request = null)
    {
        if (!$request) {
            $request = container()->get('request');
        }
        $static = new static($request->getUri()->getParseQuery(), $request);
        return $static->getApi();
    }

    public function ajax(){
        return new AjaxApi($this->get,  include_once API_STRUCTURE_DIR);
    }

    public function api(){
        return new Api($this->get);
    }

    public function getApi()
    {
        if($this->get['class'] === 'Api'){
            return $this->ajax();
        }
        return $this->api();
    }
}