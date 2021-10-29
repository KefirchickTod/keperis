<?php


namespace src\Core\Filtration;

use Exception;

class DateFilter extends DataFilterPrototype implements FilterInterface
{

    private $brackets = [];
    /**
     * @var bool
     */
    private $dateCheck = false;
    /**
     * @var array
     */
    private $listOfSpecialDateName =
        [
            'bc_user_birthday',
        ];

    /**
     * @var array
     */
    private $basicFilter =
        [
            'empty'  => '(%_val_%     IS NULL OR %_val_% = ""   OR   %_val_% = "0")',
            'filled' => '(%_val_%    IS NOT NULL     AND %_val_%  != ""  AND %_val_% != "0")',
        ];
    /**
     * @var array
     */
    private $exception =
        [
            'eventStatus' =>
                [
                    '1' => '       bc_event_public    IS    NULL',
                    '2' => '       bc_event_date    <    NOW() ',
                    '3' => '       bc_event_date    >    NOW()  AND bc_event_public  IS   NOT   NULL',
                ],
            'grantStatus' =>
                [
                    'empty'  => 'bc_grant_public = 5',
                    'filled' => 'bc_grant_id <> 0',
                    '1'      => '    DATE_FORMAT(bc_grant_date_finish, "%Y%m%d" ) < DATE_FORMAT( NOW(), "%Y%m%d" )',
                    '2'      => '       DATE_FORMAT(bc_grant_date_finish, "%Y%m%d" ) < DATE_FORMAT( NOW(), "%Y%m%d" )  ',
                    '3'      => '       DATE_FORMAT(bc_grant_date_finish, "%Y%m%d" ) >= DATE_FORMAT( NOW(), "%Y%m%d" )  ',
                ],
            'pgStatus'    =>
                [
                    'filled' => '      pst.bc_dictionary_title_uk     IS     NULL',
                    'empty'  => '       pst.bc_dictionary_title_uk       IS      NOT         NULL',
                    '1'      => '    pst.bc_dictionary_title_uk   LIKE    "%aктивний%"',
                    '2'      => '     DATEDIFF( NOW( ), bc_user_is_participant_till )     >   30        ',
                    '3'      => '        DATEDIFF( NOW( ), bc_user_is_participant_till )     >      0    AND   DATEDIFF( NOW( ), bc_user_is_participant_till )     <   30   ',
                    '4'      => 'pst.bc_dictionary_title_uk   LIKE    "%активний, не підтвердж.%"',
                    '5'      => 'pst.bc_dictionary_title_uk   LIKE    "%активний, підтвердж.%"',
                    '6'      => 'pst.bc_dictionary_title_uk   LIKE    "%деакт. користувачем%"',
                ],
            'timerMode' => [
                '-1' => '   bc_user_leaders_cout_timer    =     "0"     '
            ],
        ];

    private $type = 'date';
    /**
     * @var string
     */
    private $date;
    /**
     * @var array
     */
    private $result;

    public function searchDate($column = null)
    {
        $result = [];
        $column = !$column ? $this->where : $column;
        $where = is_array($this->where) ? $this->where[0] : $this->where;
        foreach ($column as $value) {
            $result[] = "$where = '$value'";
        }

        return '(' . implode(" OR ", $result) . ')';
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getResult()
    {
        if (empty($this->result)) {
            foreach ($this->error as $value) {
                $err['error'][] = $value;
            }
            header("Location: /" . $_GET['bcurl']);
            die("Some Error in Parse");
            return;
        }

        if ($this->brackets) {
            foreach ($this->result as $name => $value) {
                if (in_array($name, $this->brackets)) {
                    $this->result[$name] = "($value)";
                }
            }
        }
        return implode("   " . parent::getDelimetr() . "   ", $this->result);
    }


    /**
     * @param $from
     * @param $to
     * @return string
     */
    public function creatFromTo($from, $to, $parent = null)
    {
        try {
            $result = [];
            if (!$parent) {
                foreach ($this->where as $name => $value) {
                    if ($this->type == $name) {
                        if (is_array($value)) {
                            foreach ($value as $val) {
                                $result[] = "$val BETWEEN '$from' AND '$to'";
                            }
                        }
                    }
                    // $result[] = $this->type == $name ?"$value BETWEEN '$from' AND '$to'" :null;
                }
            } else {
                $result[] = "$parent BETWEEN '$from' AND '$to'";
            }
            if (!array_diff($result, [null, '', false])) {
                throw new Exception("Empty result");

            }
        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }
        return implode(' OR ', array_diff($result, [null, false, '']));
    }

    /**
     * @param $filter
     * decode_json filter
     * @return $this
     */
    public function parseFilter($filter)
    {

        try {

            if (!$filter) {
                throw new  Exception('Empty filter');
            }
            foreach ($filter as $name => $filterValue) {
                $filterValue = $filterValue === '0' ? 'empty' : $filterValue;
                $filterValue = is_array($filterValue) ? array_diff($filterValue, [null, '', false]) : [$filterValue];
                $filterValue = $this->checkOnInt($filterValue[0]);
                foreach ($filterValue as $val) {
                    $this->getType($val, true);
                    $select = $this->checkDictionary($this->getSelectByColumnName(trim($name)));
                    $select = in_array($select, $this->listOfSpecialDateName) ? $this->specialFilterForDate($val,
                        $select)[0] : $select;
                    $insert = $this->dateCheck != true ? $this->setType($select, $val, trim($name)) : $select;
                    //var_dump($insert);
                    if (in_array($val, array_keys($this->basicFilter))) {
                        $insert = str_replace('%_val_%', $select, $this->basicFilter[$val]);

                    }

                    if (in_array($name, array_keys($this->exception))) {
                        $insert = $this->exception[$name][$val] ?? $insert;

                    }

                    if (count(array_diff(explode(',', $filter[$name]), ['', null, false])) > 1) {
                        $this->result = addToArray($this->result, $name, $insert, ' OR ');
                        $this->brackets[] = $name;
                    } else {
                        addToArray($this->result, $name, $insert);
                    }


                }
            }

            if (!$this->result) {
                throw new Exception("No result");
            }
            $this->result = array_unique(array_diff($this->result, ['', null]));

        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }
        return $this;
    }

    /**
     * @param $value
     * @return array
     */
    private function checkOnInt($value)
    {
        if (explode(',', $value)) {
            return array_diff(explode(',', $value), [null, '', false]);
        }
        return $value;
    }

    /**
     * @param $select
     * @return string
     */
    public function checkDictionary($select, $check = true)
    {
        if ($this->getType(null) == 'string' && $check == true) {
            return $select;
        }
        if (preg_match("~bc_dictionary_title_uk~", $select)) {
            $prefix = explode('.', $select)[0];
            $select = $prefix . '.bc_dictionary_id';

        }
        if (preg_match("~bc_roles_title~", $select)) {
            $select = "bc_roles_id";
        }
        return $select;
    }

    /**
     * @param $value
     * @param $name
     * @return array
     */
    public function specialFilterForDate($value, $name)
    {
        switch ($value) {
            case -1:
                break;
            case 'empty':
            case 'filled':
                $column_filters[] = "$name";
                break;
            case 'today':
                $column_filters[] = "MONTH($name) = MONTH(CURDATE()) AND DAY($name) = DAY(CURDATE())";
                break;
            case 'tomorrow':
                $column_filters[] = "MONTH($name) = MONTH(ADDDATE(CURDATE(), 1)) AND
                                DAY($name) = DAY(ADDDATE(CURDATE(), 1))";
                break;
            case 'week':
                // https://stackoverflow.com/a/18748137/5854085
                $column_filters[] = "(DAYOFYEAR($name) >= 7 AND DAYOFYEAR($name) - DAYOFYEAR(CURDATE())
                                BETWEEN '0' AND 6) OR (MOD(YEAR(CURDATE()), 4) = 0) AND MOD(YEAR(CURDATE()), 100) != '0' 
	                            AND (DAYOFYEAR($name) + 366 - DAYOFYEAR(CURDATE())) % 366 < 7 OR (DAYOFYEAR($name) +
	                            365 - DAYOFYEAR(CURDATE())) % 365 < 7";
                break;
            case 'month':
                $column_filters[] = "MONTH($name) = MONTH(CURDATE())";
                break;
            default:
                $column_filters[] = "MONTH($name) = $value";
        }

        $this->dateCheck = true;
        return $column_filters;
    }

    /**
     * @param $select
     * @param $value
     * @return string|void
     * @throws Exception
     */
    private function setType($select, $value, $column)
    {

        try {
            if ($value == '' || empty($value)) {
                return '';
            }
            $filterPattern = $this->checkFilterTamplates($column, $value);
            if ($filterPattern != false) {
                //var_dump($this->checkFilterTamplates($column,$value));
                return $filterPattern;
            }

            $type = $this->getType($select);
            if (preg_match("~bc_dictionary_id~", $select)) {
                return "$select = $value";
            }
            if ($type === 'string' || $type === 'email') {
                return "$select   LIKE   '%$value%'";
            } elseif ($type === 'int') {
                return "$select    =    $value";
            } else {
                return "$select    =   '$value'";
            }
            throw new Exception("NO type Error");
        } catch (Exception $error) {
            $this->setError($error, __CLASS__);
        }
        return;

    }

    public function getExeptionFilter()
    {
        return $this->exception;
    }

    public function setExeptionFilter($exeption)
    {
        $this->exception = array_merge($exeption, $this->exception);
        //debug($this->exception);
        return $this;
    }

}
