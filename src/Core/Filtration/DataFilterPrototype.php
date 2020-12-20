<?php


namespace src\Core\Filtration;

use src\Core\Filtration\ProvideStructure;
use Exception;

class DataFilterPrototype extends ProvideStructure
{

    /**
     * @var bool
     */
    private $filterTamplates = false;

    /**
     * @var string
     */
    private $delimetr = "AND";
    /**
     * @var string
     */
    private $type = null;
    /**
     * @var array
     */
    private $pattarns = [];
    /**
     * @var array
     */
    protected $where = [];
    /**
     * @var array;
     */
    protected $bcClass;

    /**
     * @var array
     */
    protected $dataStructure;
    /**
     * @var array
     */
    protected $error = [];


    public function __clone()
    {
        $this->error = [];
        $this->where = [];
        $this->pattarns = [];
        $this->type = null;
    }

    /**
     * @param $dataStructure
     * @return $this
     * @throws Exception
     */
    public function setDataStructure(&$dataStructure)
    {
        try {
            $this->dataStructure = $dataStructure;
            $this->bcClass = $this
                ->setStructure(
                    $this->dataStructure
                )->getClassFromMethod();

            if (!$this->dataStructure || !$this->bcClass) {
                throw new Exception("No current data");
            }
        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }
        return $this;
    }

    /**
     * @return array|null
     */

    private function allGetOption() : array
    {
//        foreach ($this->dataStructure[$this->mainName]['get'] as $value) {
//
//            if (isset($this->dataStructure[$this->mainName]['setting']['join'])) {
//                foreach ($this->dataStructure[$this->mainName]['setting']['join'] as $joiner) {
//
//                    $joiner = (isset($joiner[key($joiner)]['get']) && is_array($joiner[key($joiner)]['get'])) ? $joiner[key($joiner)]['get'] : $joiner['get'];
//                    foreach ($joiner as $valGet) {
//                        $this->get[] = $valGet;
//                    }
//                    //array_map([$this, 'allGetOption'], $joiner);
//                }
//            }
//            $this->get[] = $value;
//        }
        $get = [];
        foreach ($this->get as $value){
            if(is_array($value)){
                $get = array_merge($get, $value);
            }
            else{
                $get[] = $value;
            }
        }

        return array_unique($get);

    }

    public function parse()
    {
        try {

            foreach ($this->getPatternList() as $value) {
                foreach ($value as $name => $val) {
                    if (isset($val['type'])) {
                        if (is_array($val['type'])) {
                            foreach ($val['type'] as $multitype) {
                                $this->where[$multitype][] = in_array($name,
                                    $this->allGetOption()) ? $val['select'] : null;
                            }
                        } else {
                            $this->where[$val['type']][] = in_array($name,
                                $this->allGetOption()) ? $val['select'] : null;
                        }
                    } else {
                        $this->where[] = in_array($name, $this->allGetOption()) ? $val['select'] : null;
                    }
                }
            }
            if (!$this->where) {
                throw new Exception("Empty Where");

            }

            $result = [];
            foreach ($this->where as $name => $value) {
                if (!is_int($name) && !empty($value)) {
                    $result[$name] = array_diff($this->where[$name], [null, false, '']);
                }
            }


            $this->where = $result;

            //var_dump($this->where);exit;
        } catch (Exception $error) {
            $this->error[] = $error->getMessage();
        }
        return $this;
    }

    /**
     * @return array
     */
    protected function getPatternList()
    {
        $pattern = [];
        foreach ($this->bcClass as $class) {
            $pattern[] = $class->getPattern();
        }
        $this->pattarns = $pattern;
        return $pattern;
    }

    /**
     * @return array
     */
    public function getDataStructure()
    {
        try {
            if (!$this->dataStructure) {
                throw new Exception("No data stucture");
                var_dump($this);
                exit;
            }
        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }
        return $this->dataStructure;
    }


    protected function setError(Exception $error, $class)
    {
        $this->error[$class] = $error->getMessage();
    }

    /**
     * @param $colomn
     * @return mixed|null
     */
    public function getSelectByColumnName($column)
    {
        try {
            if (!$this->pattarns) {
                $this->pattarns = $this->getPatternList();
            }
            foreach ($this->pattarns as $value) {
                $get[] = in_array($column, array_keys($value)) ? $value[$column]['select'] : null;
            }

            if (empty($get) || !$get) {
                throw new Exception("Columnn no in pattern");
            }
        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }

        return implode('', array_diff(
            array_unique($get)
            , [null, false, '']));
    }




    protected function getType($typeLine, $new = false)
    {
        if ($this->type && $new == false) {
            return $this->type;
        }
        $validatePhone = function () use ($typeLine) {
            $justNums = preg_replace("/[^0-9]/", '', $typeLine);
            if (strlen($justNums) == 11) {
                $justNums = preg_replace("/^1/", '', $justNums);
            }
            if (strlen($justNums) == 10) {
                return true;
            }
            return false;

        };

        if (filter_var($typeLine, FILTER_VALIDATE_EMAIL)) {
            $this->type = 'email';
            return 'email';
        }
        if (filter_var($typeLine, FILTER_VALIDATE_INT) || $validatePhone() == true) {
            $this->type = 'int';
            return 'int';
        }
        if (strtotime($typeLine)) {
            $this->type = 'date';
            return 'date';
        }
        $this->type = 'string';
        return 'string';
    }

    /**
     * @param $delimtr
     * @return $this
     */
    public function setDelimtr($delimtr)
    {
        $this->delimetr = $delimtr;
        return $this;
    }

    /**
     * @return string
     */
    public function getDelimetr()
    {
        return $this->delimetr;
    }

    public function checkFilterTamplates($column, $filterValue)
    {
        $filterValue = is_array($filterValue) ? implode(' ', $filterValue) : $filterValue;
        foreach ($this->getPatternList() as $value)
        {
            if(in_array($column, array_keys($value))){
                if(isset($value[$column])){

                    return isset($value[$column]['filter']) ? str_replace("%_value_%", $filterValue, $value[$column]['filter']) : false;
                }
            }
        }
        return false;

    }

    function __debugInfo()
    {
        var_dump($this->error);
    }


}