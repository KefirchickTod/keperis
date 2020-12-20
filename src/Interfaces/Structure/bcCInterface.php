<?php

namespace src\Interfaces\Structure;


interface ProvideStructures
{
    public function getPattern($key = false): array;

    public function getTableName(): string;

    public function getFactoryName();
}