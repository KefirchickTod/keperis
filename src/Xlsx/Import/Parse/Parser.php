<?php


namespace src\Xlsx\Import\Parse;

use src\Collection;
use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Models\Model;

/**
 * Class Parser
 * @package src\Xlsx\Import\Parse
 * @varsion 0.2
 * @author Zahar
 */
class Parser implements XlsxParseInterface
{

    const PARSER_INDITIFACTION = 'indetification';
    const PARSER_MARKER = 'new_add';
    const PARSE_STATUS_UNIQUE = 1;
    const PARSE_STATUS_NO_UNUQIE = 0;

    const CUT_LENGTH = 500;
    /**
     * @var array
     * Array of titles from import data
     */
    private $title;
    /**
     * @var Model
     * Extened models
     */
    private $model;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $dataFromDb;


    /**
     * @param Collection $data
     * @param Model $model
     * Prepare and start parse dat
     */
    public function parse(Collection $data, Model $model)
    {

        $this->model = $model;

        if ($data->has('title')) {
            $this->title = $data->get('title');
        } else {
            $this->title = $this->parseTitle($data->first());
            $data->remove(0);
        }

        if ($data->has('unique') || $data->has(self::PARSER_MARKER)) {
            $this->data = [
                'unique'            => $data->get('unique'),
                self::PARSER_MARKER => $data->get(self::PARSER_MARKER),
            ];
        } else {
            $this->dataFromDb = $this->getValuesFromDb();
            $this->data = $this->parseData((array)$data->toArray());
        }

    }

    /**
     * @param $title
     * @return array
     * Create title template for table
     */
    private function parseTitle($title)
    {
        $data = [
            'event_o' => [
                'text' => 'Дії',
            ],
        ];
        $label = $this->model->getXlsxMargeColumn();
        foreach ($title as $value) {
            $value = slug($value);
            if(array_key_exists($value, $label)){
                $data[$value] = ['text' => $label[$value]['label'], 'width' => '100px'];
            }else{
                $data[$value] = ['text' => $value, 'width' => '100px'];
            }
        }
        return $data;
    }

    /**
     * @return array
     * Get values from db
     */
    private function getValuesFromDb()
    {
        $data = (array)$this->model->where('bc_user_delete <> 1')->toArray();
        return $data;
    }

    private function parseData(array $data)
    {

        $data = array_slice($data, 0, self::CUT_LENGTH);

        $titleKeys = array_keys($this->title);
        $result = [];
        $unique = [];


        $size = count($data);





        for ($row_key = 0; $row_key <= $size; $row_key++) {
            if (!isset($data[$row_key])) {
                continue;
            }
            $row = $data[$row_key];
            $isFindUniqueValue = false;
            foreach ($row as $key => $value) {
                $result[$row_key][self::PARSER_INDITIFACTION] = $row_key;
                if(!array_key_exists(($key+1), $titleKeys)){
                    break;
                }
                $outeKey = $titleKeys[$key + 1];


                $result[$row_key][$outeKey] = $this->model->convertImportType($outeKey, $value);





                $value = trim($value);

                if (!empty($value) && $isFindUniqueValue === false && $this->isOuterKeyUnique($outeKey)) {

                    $unique[$outeKey][$row_key] = $value;
                    //$isFindUniqueValue = true;
                }
            }
        }



        $result = $this->parseUnique($unique, $result);

        return $result;
    }



    private function isOuterKeyUnique($key)
    {
        if ($this->model->getImportUnique($key)) {
            return true;
        }
        return false;
    }


    private function parseUnique($unique, $data)
    {
        $result = [];


        $pareseItem = function ($item, $ids, &$result) use ($data){

            $status = [];


            foreach ($item as $rowIndex => $value) {

                if (array_key_exists($value, $ids)) {
                    $data[$rowIndex]['id'] = $ids[$value];

                    $result['unique'][$rowIndex] = $data[$rowIndex];
                    $status[self::PARSE_STATUS_UNIQUE][] = $rowIndex;


                } else {
                    $result[self::PARSER_MARKER][$rowIndex] = $data[$rowIndex];
                    $result[self::PARSER_MARKER][self::PARSER_MARKER] = true;
                    $status[self::PARSE_STATUS_NO_UNUQIE][] = $rowIndex;

                }
            }
           // var_dump($status);

            return $status;

        };



        //var_dump($unique);exit;
        foreach ($unique as $column => $item) {



            $item = array_map(function ($val) use($column){
                return $this->model->convertImportType($column, $val);
            }, $item);
            if(is_array($this->model->getImportUnique($column))){
                $uniqueColumns = $this->model->getImportUnique($column);
                $status = [];
                foreach ($uniqueColumns as $col){

                    $ids = array_column($this->dataFromDb, $this->model->id(), $col);

                    $stat = ($pareseItem($item, $ids, $result));
                    $status = array_merge($stat, $pareseItem($item, $ids, $result));
                }

                if($status && array_key_exists(self::PARSE_STATUS_UNIQUE, $status)){
                    foreach ($status[self::PARSE_STATUS_UNIQUE] as $rowIndex){
                        if(!array_key_exists('unique', $result)){
                            break;
                        }
                        if(array_key_exists($rowIndex, $result[self::PARSER_MARKER]) && array_key_exists($rowIndex, $result['unique'])){
                            unset($result[self::PARSER_MARKER][$rowIndex]);
                        }
                    }

                }


            }else{
                $ids = array_column($this->dataFromDb, $this->model->id(), $this->model->getImportUnique($column));
                $pareseItem($item, $ids, $result);


            }
        }
        if(isset($result['unique']) && isset($result[self::PARSER_MARKER])){
            $keys  = array_intersect_key($result['unique'], $result[self::PARSER_MARKER]);

            if($keys){
                $keys = array_keys($keys);
                foreach ($keys as $key){
                    if(array_key_exists($key, $result[self::PARSER_MARKER]) && array_key_exists($key, $result['unique'])){
                        unset($result[self::PARSER_MARKER][$key]);
                    }
                }
                if(sizeof($result[self::PARSER_MARKER]) <= 1){
                    $result[self::PARSER_MARKER][self::PARSER_MARKER] = false;
                }
            }

        }
       // var_dump($result);exit;



        return $result;
    }

    public function getTitle(): array
    {
        return $this->title;
    }

    public function getUniqueData()
    {

        return $this->data['unique'] ?? null;
    }

    public function getDataForAdd()
    {
        return $this->data[self::PARSER_MARKER] ?? null;
    }

    public function getData(): array
    {

        $result = [];
        foreach ($this->data as $value){
            $result = array_merge($value, $result);
        }
        return $result;
    }

    public function toArray()
    {

        $data = $this->data;
        $data['title'] = $this->title;

        return $data;
    }

    private function prepareDataToMerge($arr)
    {
        $result = [];
        foreach ($arr as $key => $value) {
            $result[$key + 1] = $value;
        }
        return $result;
    }
}