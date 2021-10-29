<?php


namespace src\Core\RendererData;


use App\Provides\Mask;
use src\Http\Request;
use src\Structure\Structure;

class RenderFactory
{

    protected $input = [];

    protected $action;
    protected $title;
    protected $data;
    /**
     * @var Structure
     */
    protected $structure;

    /**
     * @param Mask $mask
     * @param string $title
     * @param string $maskTitle
     * @param string $action
     * @return RenderFactory|static
     */
    public static function createRenderer(Request $request, Mask $mask, $title = '', $maskTitle = "", $action = "" ){
        $title = $mask->getTitle($title);
        $dataArray = $mask->getMask($maskTitle);
        $action = $mask->getAction($action);
        $input =  $request->getUri()->getParseQuery();
        $static = new static($dataArray, $title, $action);
        if($static->isEmpty()){
            throw  new RuntimeException("Get empty parrams");
        }
        return $static;
    }

    public function __construct(array $data,array $title, array $action, $input = null)
    {
        $this->data = $data;
        $this->title = $title;
        $this->action = $action;
        $this->input = $input;
        $this->structure = \structure()->set($data);
    }


    public function withTitle(array $title){
        $clone = clone $this;
        $clone->title = $title;
        return $clone;
    }

    public function withDataArray(array  $data){
        $clone = clone $this;
        $clone->data = $data;
        return $clone;
    }

    public function withAction(array $action){
        $clone = clone $this;
        $clone->action = $action;
        return $clone;
    }

    public function withInput(array $input){
        $clone = clone $this;
        $clone->input = $input;
        return $clone;
    }

    public function isEmpty(){
        return !$this->title || !$this->data;
    }

}