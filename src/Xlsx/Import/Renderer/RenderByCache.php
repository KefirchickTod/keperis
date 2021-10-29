<?php


namespace src\Xlsx\Import\Renderer;


use src\Collection;
use src\Interfaces\Xlsx\XlsxValidationInterface;

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