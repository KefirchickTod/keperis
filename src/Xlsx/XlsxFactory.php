<?php


namespace src\Xlsx;


use src\Core\provideExport;
use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Interfaces\Xlsx\XlsxRenderInterface;
use src\Interfaces\Xlsx\XlsxValidationInterface;
use src\Model;
use src\Xlsx\Import\ImportFile;
use src\Xlsx\Import\Renderer\Render;
use src\Xlsx\Import\Renderer\RenderByCache;

/**
 * Class XlsxFactory
 * @package src\Xlsx
 */
class XlsxFactory
{


    /**
     * @param Model $model
     * @param XlsxParseInterface $parse
     * @return XlsxRenderInterface
     */
    public static function renderByCache(Model $model, XlsxParseInterface $parse)
    {
        return new RenderByCache($model, $parse, null);
    }


    /**
     * @param Model $model
     * @param XlsxParseInterface $parse
     * @param string $file
     * @return XlsxRenderInterface
     */
    public static function render(Model $model, XlsxParseInterface $parse, string $file)
    {
        return new Render($model, $parse, $file);
    }

    /**
     * @param $file
     * @param Model $model
     * @param XlsxRenderInterface $render
     * @return ImportFile
     */
    public static function import(XlsxValidationInterface $validation, XlsxRenderInterface $render)
    {

        return new ImportFile($validation, $render);
    }

    /**
     * @return provideExport
     */
    public static function export()
    {
        return new provideExport();
    }
}