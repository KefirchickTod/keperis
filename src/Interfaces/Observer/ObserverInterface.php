<?php


namespace Src\Interfaces\Observer;


interface ObserverInterface
{

    public function update(string $event, $emitter, $data = null);
}