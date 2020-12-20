<?php


namespace src\Core\Table;

use src\Core\Table\eeTablePrototype;
use src\Core\Table\eeCaption;
use src\Core\Table\eeRow;
use src\Core\Table\eeCell;

class eeTable extends eeTablePrototype
{
    protected $table = '';
    protected $caption;
    protected $rows = [];
    private $rowIndex = 0;

    private $tAttr =
        [
            'thead' => '',
            'tbody' => '',
            'tfoot' => '',
        ];

//    protected $cellIndexesByRow = [];

    public function newCaption()
    {
        return new eeCaption();
    }

    public function newRow($index = null)
    {
        return new eeRow($index);
    }

    public function newCell()
    {
        return new eeCell();
    }

    public function addCaption(eeCaption $caption)
    {
        $this->caption = $caption;
    }

    public function addRow(eeRow $row, $index = null)
    {
        if ($index == null || $row->getIndex() == null) {
            $index = $this->generateRowIndex();
            $row->setIndex($index);
        }

        $this->rows[$row->getIndex()] = $row;
    }

    protected function generateRowIndex()
    {
        $this->rowIndex++;

        return $this->rowIndex;
    }

    public function __toString()
    {
        $this->generateTable();

        return $this->table;
    }

    protected function generateTable()
    {
        $this->table = '';
        $this->table .= '<table';
        $this->table .= $this->getClasses();
        $this->table .= $this->getStyle();
        $this->table .= $this->getAttr();
        $this->table .= '>';

        $tableContent = [
            'caption' => '',
            'thead'   => [],
            'tbody'   => [],
            'tfoot'   => [],
        ];

        if (is_object($this->caption)) {
            $tableContent['caption'] = $this->caption->__toString();
        }


        if (!empty($this->rows)) {
            foreach ($this->rows as $row) {
                $tableContent[$row->getAffiliation()][] = $row->__toString();
            }
        }

        $this->table .= $tableContent['caption'];
        $this->table .= $tableContent['thead'] ? '<thead ' . $this->tAttr['thead'] . '>' . implode(PHP_EOL,
                $tableContent['thead']) . '</thead>' : '';
        $this->table .= $tableContent['tbody'] ? '<tbody ' . $this->tAttr['tbody'] . '>' . implode(PHP_EOL,
                $tableContent['tbody']) . '</tbody>' : '';
        $this->table .= $tableContent['tfoot'] ? '<tfoot ' . $this->tAttr['tfoot'] . '>' . implode(PHP_EOL,
                $tableContent['tfoot']) . '</tfoot>' : '';

        $this->table .= '</table>';
    }

    public function setTheadAttr($attr)
    {
        $this->tAttr['thead'] = $attr;
        return $this;
    }

    public function setTBodyAttr($attr)
    {
        $this->tAttr['tbody'] = $attr;
        return $this;
    }

    public function setTFootAttr($attr)
    {
        $this->tAttr['tfoot'] = $attr;
        return $this;
    }

}