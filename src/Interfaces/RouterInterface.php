<?php


namespace src\Interfaces;


use src\Controller\Controller;

interface RouterInterface
{

    public function getController($counting = null): Controller;

    public function fullPath($path);

    public function parse($path): array;

    public function map(array $method, array $pattern, $controller, $methods);
}