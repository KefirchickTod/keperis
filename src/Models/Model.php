<?php


namespace src\Models;



use src\Structure\ProvideStructures;
use src\Structure\Structure;
use src\Traits\TableTrait;
use Error;
use Exception;
use src\Model as BaseModel;

abstract class Model extends BaseModel
{
    use TableTrait;


    public function __construct(array $arguments = null, $data = null)
    {
        parent::__construct($arguments ?: [], $data ?: []);
    }

    public static function __callStatic($name, $arguments)
    {
        return (new static())->$name($arguments);
    }

    /**
     * @param ProvideStructures $provideStructures
     * @return static
     */
    protected function withProvides(ProvideStructures $provideStructures){
        $clone = clone $this;
        $clone->bc_table = $provideStructures->getTableName();
        return $clone;
    }

    public function setArrayAtributes(array $atributes){
        foreach ($atributes as $key => $atribute){
            $this->setAttribute($key, $atribute);
        }
        return $this;
    }

}