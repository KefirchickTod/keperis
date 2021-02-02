<?php


namespace src\Controller\Api;


use src\Container;

class ErrorApi extends ApiController
{

    protected $msg = "";

    public function __construct($msg = "")
    {
        parent::__construct();
        $this->msg = $msg;
    }

    public function failed(){
        return [
            'error'  => $this->msg
        ];
    }

    /**
     * @param string|string[] $msg
     * @return ErrorApi
     */
    public function withMassage($msg){
        $clone = clone $this;
        $clone->msg = $msg;
        return $clone;
    }
}