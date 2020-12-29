<?php


namespace src\Core\Page\Table;


use src\Core\Table\eeTable;
use src\Structure\ProvideFilter;

/**
 * Class Render
 * @package src\Core\Page\Table
 * @property $setting
 * @property $title
 * @property $action
 * @property ProvideFilter|array|null $filter
 */
class ProvideTableContainer
{

    /**
     * @var eeTable
     */
    private $eeTable;
    /**
     * @var array|null
     */
    private $setting;
    /**
     * @var array|null
     */
    private $title;

    private $action;

    private $filter;


    private $callbackRow = null;
    private $callbackValue = null;
    private $row;

    public function __construct()
    {
        $this->eeTable = new eeTable();
    }


    public function execute(array $setting = [], array $title = [], $filter = [], array $action = [])
    {
        $this->setSettings($setting);
        $this->setTitle($title);
        $this->setFilter($filter);
        $this->setAction($action);

    }


    public function setSettings($settings)
    {
        $this->setting = $settings;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function __get($name)
    {

        if ($this->has($name)) {
            return $this->$name;
        }
        return null;
    }

    public function __set($name, $value)
    {

        if ($this->has($name)) {
            $this->$name = $value;
        }
        throw new \RuntimeException("Cant find var ($name) in collection");
    }

    public function has($name)
    {
        if (isset($this->$name)) {
            return true;
        }
        return false;
    }

    /**
     * @param null|\Closure $closure
     */
    public function callbackRow($closure)
    {
        $this->callbackRow = $closure;
    }

    /**
     * @param \Closure $closure
     */
    public function callbackValue(\Closure $closure)
    {
        $this->callbackValue = $closure;
    }

    /**
     * @return null|\Closure
     */
    public function getCallbackRow()
    {
        return $this->callbackRow;
    }

    /**
     * @return null|\Closure
     */
    public function getCallbackValue()
    {
        return $this->callbackValue;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function setRow($row)
    {
        $this->row = $row;
    }

}