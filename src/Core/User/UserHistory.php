<?php


namespace src\Core\User;


use src\Core\Page\PageCreator;
use src\Core\Page\ProvideTable;
use src\Http\Request;
use src\Model;
use App\Provides\Mask;
use src\Structure\ProvideFilter;
use src\Structure\ProvideStructures;
use src\Structure\Structure;

class UserHistory
{

    protected $data = [];
    private $mask;
    private $title;

    public $action = [];
    private $fetch;

    /**
     * @var Structure
     */
    private $structure;

    public function __construct(Mask $mask = null, $key = 'user')
    {

        $this->data = $mask->getMask($key);
        $this->mask = $mask;
        $this->title = $mask->getTitle('default');
        $this->structure = structure();
    }

    public function cleanStructure(){
        $this->structure->clean();
    }

    
    public function setDataArray($data){
        $this->data = $data;
        return $this;
    }

    public function withMask(Mask $mask)
    {
        $clone = clone $this;
        $clone->mask = $mask;
        return $clone;
    }

    public function withTitle(string $key)
    {
        $clone = clone $this;
        $clone->title = $clone->mask->getTitle($key);

        return $clone;
    }

    public function prepare(\Closure $callback)
    {
        foreach ($this->data as $key => $value) {
            $this->data[$key] = call_user_func($callback, $key, $value);
        }
        $this->structure->set($this->data);
    }

    public function get($key, \Closure $callback = null)
    {

        if ($callback) {
            return $this->structure->getData($callback, $key);
        }

        return $this->structure->get($key);
    }

    private function addFilter($row){
        if(isset($this->title['source'])){
            $source = array_unique(array_column($row, 'source'));
            if(in_array('Коментар', $source)){
                $key = array_search('Коментар', $source);
                if($key === 0){
                    unset($source[0]);
                    $source[] = 'Коментар';
                }
            }elseif(isset($source[0])){
                $val = $source[0];
                unset($source[0]);
                $source[] = $val;
            }
            $this->title['source']['filter']['array'] = $source;
         //   debug($source, array_unique(array_column($row, 'source')));

        }

    }

    public function toTable(array $row, bool $datesort = true): string
    {
       // var_dump($this->title);
        $table = new ProvideTable();
        $table->select2 = "select2_history";
      //  var_dump($row);
        $this->addFilter($row);
       // debug($this->title);
        $table->setting([
            'attrTable' => 'id = "user_history_sort" class = "table table-striped table-bordered  eventFirst table-sm" style = "    table-layout: fixed;font-size: 0.9em; margin-bottom:2px;" ',
            'thead'     => 'class ="thead-light"',
        ], $this->title, new ProvideFilter(), $this->action );
        if($datesort === true){
            usort($row, function ($a, $b){
                if(isset($a['date']) && isset($b['date'])){
                    return strtotime($b['date']) - strtotime($a['date']);
                }
                return false;
            });
        }
        $table->setRow($row);

        return $table->render();

    }


}