<?php


namespace src\Core\Table;

class eeCaption extends eeTablePrototype
{

    public function __toString()
    {
        $result = '<caption';
        $result .= $this->getClasses();
        $result .= $this->getAttr();
        $result .= '>';
        $result .= implode($this->dataSeparator, $this->data);
        $result .= '</caption>';

        return $result;
    }
}