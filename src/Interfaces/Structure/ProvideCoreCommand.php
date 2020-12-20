<?php


namespace src\Interfaces\Structure;


use App\Core\Provide\Structure;

interface ProvideCoreCommand
{

    public function getClassName(): string;

    public function getQuery(): string;

    public function getTime(): string;

    public function getMemory(): int;

    public function getStatus(): int;

    public function execute(): void;

}