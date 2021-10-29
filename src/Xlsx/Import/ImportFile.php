<?php


namespace src\Xlsx\Import;


use src\Core\Cache;
use src\Interfaces\Xlsx\XlsxImportInterface;
use src\Interfaces\Xlsx\XlsxRenderInterface;
use src\Interfaces\Xlsx\XlsxValidationInterface;
use src\Models\Model;
use src\Xlsx\Validation\Validate;

/**
 * Class Import
 * @package src\Xlsx
 * @author Zahar Pylypchiuck
 * @version 0.0.1
 */
class ImportFile implements XlsxImportInterface
{

    /**
     * @var XlsxValidationInterface
     */
    private $validator;

    /**
     * @var XlsxRenderInterface
     */
    private $render;


    public function __construct(
        XlsxValidationInterface $validator,
        XlsxRenderInterface $render
    ) {
        $this->validator = $validator;
        $this->render = $render;
        $this->proccess();
    }


    protected function proccess()
    {
        $this->render->execute(new Validate());

    }

    public function table()
    {
        return $this->render->render();
    }

    /**
     * @return XlsxRenderInterface
     */
    public function renderer()
    {
        return $this->render;
    }

    public function buttons()
    {
        return $this->render->getButtons();
    }

    public function count()
    {
        return $this->render->count();
    }


}