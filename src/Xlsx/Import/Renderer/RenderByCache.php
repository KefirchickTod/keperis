<?php


namespace src\Xlsx\Import\Renderer;


use src\Collection;
use src\Core\Cache;
use src\Interfaces\View\ValidatorInterface;
use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Interfaces\Xlsx\XlsxValidationInterface;
use src\Model;
use src\Xlsx\Import\Parse\Parser;

class RenderByCache extends Render
{


    public function execute(XlsxValidationInterface $validation)
    {
        $data = $this->cache->get();

        if (!$this->parser) {
            throw new \InvalidArgumentException("Undefined parser");
        }

        $this->parser->parse(new Collection($data), $this->model);
        $this->initTable();

    }
}