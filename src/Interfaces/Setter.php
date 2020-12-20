<?php


namespace src\Interfaces;


interface Setter
{


    public function buildQuery(): string;

    public function buildQueryArray(): array;

    public function row(): array;

}