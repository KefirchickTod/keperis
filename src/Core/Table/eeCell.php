<?php


namespace src\Core\Table;

class eeCell extends eeTablePrototype
{
    protected $index = null;
    protected $affiliation;
    private $cell = '';

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function setAffiliation($affiliation = 'tbody')
    {
        $this->affiliation = $affiliation;
    }

    public function __toString()
    {
        $this->cell = '<' . ($this->affiliation == 'thead' ? 'th' : 'td');
        $this->cell .= $this->getClasses();
        $this->cell .= $this->getStyle();
        $this->cell .= $this->getAttr();
        $this->cell .= '>';

        $this->cell .= implode($this->dataSeparator, $this->data);
        $this->cell .= '</' . ($this->affiliation == 'thead' ? 'th' : 'td') . '>';

        return $this->cell;
    }
}

