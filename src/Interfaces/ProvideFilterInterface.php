<?php


namespace src\Interfaces;


interface ProvideFilterInterface
{
    public function getFilter(): array;

    public function updateTitle(array $title);

    public function setTemplate(string $temp);

}