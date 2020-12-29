<?php


namespace src\Xlsx\Import\Renderer;


use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Collection;
use src\Core\Cache;
use src\Core\Page\PageCreateButtons;
use src\Core\Page\ProvideTable;

use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Interfaces\Xlsx\XlsxRenderInterface;

use src\Interfaces\Xlsx\XlsxValidationInterface;
use src\Model;
use src\Xlsx\Import\Parse\Parser;


/**
 * Class RenderTable
 * @package src\Xlsx\Renderer
 */
class Render implements XlsxRenderInterface
{


    /**
     * @var ProvideTable
     */
    protected  $table;

    /**
     * @var Model
     */
    protected  $model;

    /**
     * @var Parser
     */
    protected  $parser;

    /**
     * @var string
     */
    private $buttons;

    /**
     * @var string
     */
    protected  $file;

    protected $cache;

    public function __construct(Model $model,XlsxParseInterface $parse, string $file = null)
    {
        $this->table = new ProvideTable();
        $this->model = $model;
        $this->file = $file;
        $this->cache = new Cache();
        $this->parser = $parse;
    }


    /**
     * @return string
     */
    public function render()
    {
        $this->table->setRow($this->parser()->getData());

        $this->buttons = $this->renderButtons();

        return $this->table->render();
    }

    /**
     * @return XlsxParseInterface
     */
    public function parser(): XlsxParseInterface
    {
        return $this->parser;
    }

    private function renderButtons()
    {

        $buttons = new PageCreateButtons();

        $buttons->setting([
            PageCreateButtons::$mainColumn => join('', [
                html()->a([
                    'text'  => 'For add',
                    'href'  => route('import.for.add'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                ])->render(true),
                html()->a([
                    'text'  => 'Unique',
                    'href'  => route('import.unique.add'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                ])->render(true),
            ]),
        ]);

        return $buttons->render();

    }

    /**
     * @return $this
     */
    public function execute(XlsxValidationInterface $validation)
    {
        if(!$validation->validate($this->file)){
            throw new \InvalidArgumentException($validation->getMassage());
        }

        $spreadsheet = (new Xlsx())->load($this->file)->getActiveSheet();
        if (!$this->parser) {
            throw new \InvalidArgumentException("Undefined parser");
        }

        $this->parser->parse(new Collection($spreadsheet->toArray()), $this->model);
        $this->initTable();


        $this->cache->setData($this->parser->toArray());
        $this->cache->run();

        return $this;
    }

    protected function initTable()
    {
        $style = setting()->get('page')['table'];
        $style['attrTable'] = 'class = "table table-striped table-bordered table-sm " style = "font-size: 0.9em; table-layout: fixed;height:max-content;"';
        $this->table->tablePattern([
            '{%_loadbar_%}' => '',
            "{%_mask_%}"    => 'class = "dragscroll scroll-user-table   min-height55"',
        ], '');
        $this->table->setting(setting()->get('page')['table'], $this->parser->getTitle(), null, [
            'icon'     =>
                [
                    'fa fa-user',
                    '_',
                ],
            'link'     =>
                [
                    '/user/info/%_id_%',
                    '_',
                ],
            'id'       => true,
            'callback' =>
                [
                    1 => $this->getCallbackAction(),
                ],
        ]);
    }

    protected  function getCallbackAction()
    {

        $callback = function ($action, $row, $param) {

            if (!array_key_exists('id', $row)) {
                $json = json_encode($row);
                return html()->a([
                    'text'      => '<i class="fa fa-plus"></i>',
                    'data-json' => $json,
                    'href'      => route('import.note.create') ."?id=".$row[Parser::PARSER_INDITIFACTION],
                    'id' => 'newNoteFromXLSX'
                ])->render(true);

            }
        };

        return $callback;
    }

    public function count()
    {
        return count($this->parser()->getData());
    }


    public function getButtons()
    {
        return $this->buttons;
    }
}