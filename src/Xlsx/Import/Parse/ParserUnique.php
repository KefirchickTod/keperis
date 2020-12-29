<?php


namespace src\Xlsx\Import\Parse;


class ParserUnique extends Parser
{

    public function getData(): array
    {
        return  $this->getUniqueData();
    }
}