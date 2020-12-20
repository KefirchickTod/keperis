<?php


namespace src\Core\Table;


class eeTablePrototype
{
    protected $data = [];
    protected $dataSeparator = '';
    protected $classArray = [];
    protected $index = null;
    protected $styleArray = [];
    protected $attr = '';

    public function __construct()
    {
        return $this;
    }

    public function addData($data)
    {
        $this->data[] = $data;
        return $this;
    }

    public function addStyle($style)
    {
        $this->styleArray[] = trim($style);
        $this->styleArray = array_unique($this->styleArray);
        return $this;
    }

    public function addClass($className)
    {
        $this->classArray[] = trim($className);
        $this->classArray = array_unique($this->classArray);
        return $this;
    }

    public function removeClass($className)
    {
        unset($this->classArray[trim($className)]);
        $this->classArray = array_unique($this->classArray);
        return $this;
    }

    public function getClasses()
    {
        return !empty($this->classArray)
            ? ' class="' . implode(' ', $this->classArray) . '"'
            : '';
    }

    public function getStyle()
    {
        return !empty($this->styleArray)
            ? ' style="' . str_replace(';;', ';', implode(';', $this->styleArray)) . '"'
            : '';
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    public function getAttr()
    {
        return trim($this->attr) ? ' ' . $this->attr . ' ' : '';
    }

    public function setAttr($attr)
    {
        $this->attr = $attr;
        return $this;
    }
}