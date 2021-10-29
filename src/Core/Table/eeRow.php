<?php


namespace src\Core\Table;

class eeRow extends eeTablePrototype
{
    protected $cells = [];
    private $cellIndex = 0;
    private $affiliation = 'tbody';
    private $row = '';

    public function __construct($index)
    {
        $this->index = $index;
        $this->setAsTbody();

        return $this;
    }

    public function setAsTbody()
    {
        $this->affiliation = 'tbody';

        return $this;
    }

    public function addArrayOfCells(array $cellsArray)
    {
        foreach ($cellsArray as $cell) {
            $this->addCell($cell);
        }

        return $this;
    }

    public function addCell(eeCell $cell)
    {
        if ($cell->getIndex() == null) {
            $cell->setIndex($this->generateCellIndex());
        }

        $this->cells[$cell->getIndex()] = $cell;

        return $this;
    }

    protected function generateCellIndex()
    {
        $this->cellIndex++;

        return $this->cellIndex;
    }

    public function setAsThead()
    {
        $this->affiliation = 'thead';

        return $this;
    }

    public function setAsTfoot()
    {
        $this->affiliation = 'tfoot';

        return $this;
    }

    public function getAffiliation()
    {
        return $this->affiliation;
    }

    public function __toString()
    {
        $this->generateRow();

        return $this->row;
    }

    protected function generateRow()
    {
        ksort($this->cells);

        $this->row = '';

        if (!empty($this->cells)) {

            $this->row .= '<tr';
            $this->row .= $this->getClasses();
            $this->row .= $this->getStyle();
            $this->row .= $this->getAttr();
            $this->row .= '>';
            foreach ($this->cells as $cell) {
                $cell->setAffiliation($this->affiliation);
                $this->row .= $cell->__toString();
            }
            $this->row .= '</tr>';
        }


    }
}