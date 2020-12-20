<?php


namespace src\Interfaces\View;

/**
 * Interface ValidatorInterface
 * @package src\Interfaces\View
 */
interface ValidatorInterface
{

    /**
     * @param string $file
     * @return string|null
     */
    public function validate(string $file);
}