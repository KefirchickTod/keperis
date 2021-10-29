<?php


namespace src\Core\RendererData;


use http\Exception\RuntimeException;

class RenderJson
{

    private $data;
    private $type;

    public function __construct($data, $type = 'json')
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function render(){

        if($this->type === 'json'){
            return json_decode($this->data);
        }
        if($this->type = 'serialize'){
            return serialize($this->data);
        }
        throw new RuntimeException("Undefined type");
    }
}