<?php


namespace src\View;

use src\View\Renderer\Render;
use src\View\Validator\Validator;
use src\View\View;

/**
 * Class ViewFactory
 * @package src\View
 * @author Zahar Pylypchuck
 */
class ViewFactory
{

    public static function make(string $file, $data = [])
    {
        return (new View(new Render(new Validator()), $file, $data))->withDir('layots');
    }
}