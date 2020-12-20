<?php


namespace src\Interfaces;


interface Paginator extends PageInterface
{
    public function setSetting(array $setting): void;

    public function templates(string $template);
}