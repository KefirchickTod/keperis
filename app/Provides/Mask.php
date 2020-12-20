<?php


namespace App\Provides;


abstract class Mask
{

    protected $title = [];
    protected $mask = [];
    protected $action = [];


    protected function get($array, $key = null){
        return $key ? valid($array, $key, null) : $array;
    }

    public function getTitle($key = null)
    {
        return $this->get($this->title, $key);
    }



    public function getMask($key = null)
    {
        return $this->get($this->mask, $key);
    }

    public function getAction($key = null){
        return $this->get($this->action, $key);
    }


    protected function setMaskGetValues($key, $values){
        $name = key($this->mask[$key]);

        $this->mask[$key][$name]['get'] = $values;
    }


}