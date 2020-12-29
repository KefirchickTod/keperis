<?php


namespace src\Xlsx\Import\Parse;


use src\Collection;
use src\Interfaces\CollectionInterface;
use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Model;

class Parser implements XlsxParseInterface
{


    const PARSER_INDITIFACTION = 'indetification';
    const PARSER_MARKER = 'new_add';

    const CUT_LENGTH = 500;
    /**
     * @var array
     */
    private $title;
    /**
     * @var Model
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

    private $baseData;


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


    private function parseTitle($title)
    {
        $data = [
            'event_o' => [
                'text' => 'Дії',
            ],
        ];
        foreach ($title as $value) {
            $data[slug($value)] = ['text' => $value, 'width' => '100px'];
        }
        return $data;
    }

    private function getValuesFromDb()
    {
        $data = (array)$this->model->where('')->toArray();
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
                $outeKey = $titleKeys[$key + 1];
                $result[$row_key][$outeKey] = $value;


                if ($value && $isFindUniqueValue === false && $this->isOuterKeyUnique($outeKey)) {
                    $unique[$outeKey][$row_key] = trim($value);
                    $isFindUniqueValue = true;
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

        foreach ($unique as $column => $item) {
            $ids = array_column($this->dataFromDb, $this->model->id(), $this->model->getImportUnique($column));
            foreach ($item as $rowIndex => $value) {
                if (array_key_exists($value, $ids)) {
                    $data[$rowIndex]['id'] = $ids[$value];
                    $result['unique'][$rowIndex] = $data[$rowIndex];
                } else {
                    $result[self::PARSER_MARKER][$rowIndex] = $data[$rowIndex];
                    $result[self::PARSER_MARKER][self::PARSER_MARKER] = true;
                }
            }
        }
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