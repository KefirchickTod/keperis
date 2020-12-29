<?php


namespace Src;


use src\Http\Request;

class ApiRequest
{

    private $curl;

    public static $link;

    protected $post;
    protected $get;

    private $header;

    public function __construct($get, $post = []){
        $this->curl = curl_init();
        $this->post = $post;
        $this->get = $get;
    }

    public static function send($get, $post = []){
        return new static($get, $post);
    }


    protected function getSettopCurl(){
        return [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => API_URL,
            CURLOPT_USERAGENT      => 'BCAgent',
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => $this->post,
            CURLOPT_FRESH_CONNECT  => true,
            CURLOPT_HTTPHEADER     => ['Cache-Control: no-cache'],
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_HEADER         => 0,
            CURLOPT_FOLLOWLOCATION => true,
        ];
    }

    public function addHeader(string $header){
           $this->header = $header;
    }

    public function addHeaderRequest(Request  $request){
        $header = $request->getHeaders();
        debug($header);
    }

    protected function curl(){
        return $this->curl;
    }


}