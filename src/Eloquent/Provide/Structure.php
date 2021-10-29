<?php


namespace src\Eloquent\Provide;


use src\Eloquent\Provide\Processes\ProcessorInterface;
use src\EventDispatcher\Dispatcher\Dispatcher;
use src\EventDispatcher\Provider\ListenerCollection;
use src\EventDispatcher\Provider\Provider;
use src\Interfaces\CollectionInterface;
use src\Interfaces\EventDispatcher\EventDispatcherInterface;

class Structure
{

    /**
     * @var ProvideStructure[]
     */
    private $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    /**
     * @param string $key
     * @return array[]|ProvideStructure
     */
    public function get(string $key)
    {
        if (!array_key_exists($key, $this->collection)) {
            return $this->collection[$key];
        }
        return [];
    }

    public function set($structure, string $key = '')
    {
        $provide = new ProvideStructure(new StructureCollection($key, $structure));

        $this->collection[$key ?: key($structure)] = $provide;

        return $provide;
    }
}