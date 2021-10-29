<?php


namespace src\Interfaces;


use src\Controller\Api\ApiController;

interface ApiInterfaces
{

    /**
     * @return ApiController
     */
    public function getController();

    public function getMethod();

}