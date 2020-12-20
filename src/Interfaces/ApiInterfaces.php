<?php


namespace src\Interfaces;


interface ApiInterfaces
{

    public function search();

    public function execute($result = [], $status = 202);

    public function set($arguments = []);

}