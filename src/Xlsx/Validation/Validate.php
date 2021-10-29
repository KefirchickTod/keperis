<?php


namespace src\Xlsx\Validation;


use src\Interfaces\Xlsx\XlsxValidationInterface;

class Validate implements XlsxValidationInterface
{

    public static $extension = [
        'xlsx', 'tmp'
    ];

    private $filename;
    /**
     * @var string
     */
    private $massage;


    private function fileExists(){
        return file_exists($this->filename);
    }

    private function massage(string $massage){
        $this->massage = $massage;
        return false;
    }

    public function getMassage(){
        return $this->massage;
    }

    public function validate($filename)
    {


        $this->filename = $filename;
        if(!$this->filename){
            return $this->massage("Invalid file");
        }
        if(!$this->fileExists()){
            return $this->massage("Cant find file");
        }

        $info = pathinfo($filename, PATHINFO_EXTENSION);

//        if(!in_array($info['extension'], self::$extension)){
//            return $this->massage("Error extenspion ".$info['extension'] );
//        }

        return true;
    }
}