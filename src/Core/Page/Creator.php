<?php


namespace src\Core\Page;


use src\Collection;
use src\Core\Vector;
use src\Interfaces\Buttons;
use src\Interfaces\PageInterface;
use src\Interfaces\Paginator;
use src\Interfaces\Table;
use src\Structure\Structure;

abstract class Creator
{
    /**
     * @var null|Vector
     */
    public $vector = null;
    /**
     * @var Structure
     */
    protected $structure;
    /**
     * @var array
     */
    protected $dataArray;

    function __construct(Structure $structure, array $dataArray)
    {
        $this->structure = $structure;
        $this->dataArray = $dataArray;
        $this->vector = new Vector();
    }

    public function setDataArray(array $data)
    {
        $this->dataArray = $data;
        return $this;
    }

    /**
     * @return Paginator
     */
    abstract public function getPaginator(): Paginator;

    /**
     * @return Buttons
     */
    abstract public function getButtons(): Buttons;

    /**
     * @return Table
     */
    abstract public function getTable(): Table;

    public function prepare(PageInterface $page)
    {

        $this->structure->set($this->dataArray);
        $page->setData($this->structure, $this->dataArray);
        $this->vector->set(get_class($page), $page);
    }

//    abstract public function getRendering() : PageInterface;

    public function execute(PageInterface $page): string
    {
        if ($this->vector && $this->vector->find(get_class($page))) {
            return $this->vector->find(get_class($page))->render();
        }
        $this->structure->set($this->dataArray);

        $page->setData($this->structure, $this->dataArray);
        return $page->render();
    }
}