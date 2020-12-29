<?php


namespace src\Interfaces;


use App\Api\ApiController;

interface ApiInterfaces
{

    /**
     * @return ApiController
     */
    public function getController();

    public function getMethod();

}