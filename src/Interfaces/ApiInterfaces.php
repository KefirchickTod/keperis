<?php


namespace src\Interfaces;


use App\Api\Api;

interface ApiInterfaces
{

    /**
     * @return Api
     */
    public function getController();

    public function getMethod();

}