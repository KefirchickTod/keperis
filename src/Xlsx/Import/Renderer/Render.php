<?php


namespace src\Xlsx\Import\Renderer;


use App\Models\Dictionary\DictionaryModel;
use App\Models\User\UserModel;
use App\Provides\DictionaryMask;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use src\Collection;
use src\Core\Cache;
use src\Core\Page\PageCreateButtons;
use src\Core\Page\ProvideTable;

use src\Interfaces\Xlsx\XlsxParseInterface;
use src\Interfaces\Xlsx\XlsxRenderInterface;

use src\Interfaces\Xlsx\XlsxValidationInterface;
use src\Models\Model;
use src\Xlsx\Import\Parse\Parser;
use src\Xlsx\Import\Parse\ParserForMarge;
use function foo\func;


/**
 * Class RenderTable
 * @package src\Xlsx\Renderer
 */
class Render implements XlsxRenderInterface
{


    /**
     * @var ProvideTable
     */
    protected $table;

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Parser
     */
    protected $parser;
    /**
     * @var string
     */
    protected $file;
    protected $cache;
    /**
     * @var string
     */
    private $buttons;

    public function __construct(Model $model, XlsxParseInterface $parse, string $file = null)
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
                ( $this->parser instanceof ParserForMarge ? " " :
                html()->a([
                    'text'  => 'Лише нові',
                    'href'  => route('import.for.add'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                    'target' => '_blank'
                ])->render(true) ),
                html()->a([
                    'text'  => 'Лише повтори',
                    'href'  => route('import.unique.add'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                    'target' => '_blank'
                ])->render(true),
                html()->a([
                    'text' => 'Додати всі нові',
                    'href' => route('import.auto.create'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                    'target' => '_blank'
                ]),
                html()->a([
                    'text' => 'Об\'єднати всі повтори',
                    'href' => route('import.auto.merge'),
                    'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                ]), ($this->model instanceof UserModel && !($this->parser instanceof ParserForMarge) ? html()->button(
                    [
                        'type'        => 'button',
                        'class'       => 'dt-button ui-button ui-state-default ui-button-text-only',
                        'id'          => 'ButtonGroupInvite',
                        'data-toggle' => 'modal',
                        'data-target' => '#GroupInviteImport',
                        'style'       => 'margin-bottom: 0;',
                        //'text'        => 'Групове запрошення',
                    ]

                )->span([
                    'class' => 'ui-button-text',
                    'text'  => 'Групове запрошення',
                ])->end('span')->end('button') : '')
            ]),
        ]);

        return $buttons->render();

    }

    /**
     * @param XlsxValidationInterface $validation
     * @return $this
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function execute(XlsxValidationInterface $validation)
    {
        if (!$validation->validate($this->file)) {
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
                    0 => $this->getCallbackActionForNotUnuqie(),
                    1 => $this->getCallbackAction(),
                ],
        ]);
    }

    protected function getCallbackActionForNotUnuqie(){
        return function ($action, $row, $param){
            if(!array_key_exists('id', $row)){
                return '';
            }
            return "<a target='_blank' href='".route('user.info', ['id' => $row['id']])."' target='_blank'><i class='fa fa-user'></i></a>";
        };
    }

    protected function getCallbackAction()
    {

        $callback = function ($action, $row, $param) {


            if (!array_key_exists('id', $row)) {
                $json = json_encode($row);

                return html()->a([
                    'text'      => '<i class="fa fa-plus"></i>',
                    'data-json' => $json,
                    'href'      => route('import.note.create') . "?id=" . $row[Parser::PARSER_INDITIFACTION],
                    'id'        => 'newNoteFromXLSX',
                    'target' => '_blank'
                ])->render(true);
            }else{
                return html()->a([
                    'text'      => '<i class="fa fa-code"></i>',
                    'href'      => route('import.note.merge', ['id' => $row['id']]) . "?id=" . $row[Parser::PARSER_INDITIFACTION],
                    'id'        => 'newNoteFromXLSX',
                    'target' => '_blank'
                ])->render(true);
            }
        };

        return $callback;
    }

    public function count()
    {
        return count($this->parser()->getData());
    }

    public function renderFeledsForMerge(array $fields, array $output){

        $output = ['indetification' => ''] + $output;
        $setting = $this->model->getXlsxMargeColumn();
        $result = "";
        $result .= "<div class='col-lg-6 col-md-6' id='mergeLeftWithRight'>";
        foreach ($fields as $name => $value) {
            if(!$name){
                continue;
            }
            $result .= "<div class='form-group'><label for='{$name}' class='control-label col-md-4 col-lg-4'>".($setting[$name]['label'] ?? $name)."</label>";


            if (array_key_exists($name, $setting) && !empty($value)) {
                $type = $setting[$name]['type'];
                switch ($type) {
                    case 'text':
                        $result .= $this->renderTypeText($value, $name, $setting, true);
                        break;
                    case 'select':
                        $result .= $this->renderTypeSelect($value, $name, $setting, true);
                        break;
                    case 'tel':
                        if($value[0] !== '+' && strlen($value) > 10){
                            $value = "+".$value;
                        }
                        $result .= "<input data-connect='{$setting[$name]['name']}'  readonly class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
                        break;
                    case 'multiple':
                        //var_dump($name);exit;

                        $result .= $this->renderTypeMultiple($value, $name, $setting, true);
                        break;

                    default:
                        $result .= "<input data-connect='{$setting[$name]['name']}'  readonly class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
                        break;
                }
            } else {
                $result .= "<input data-connect='$name'  readonly class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
            }


            $result .= "</div>";
        }
        $result .= "</div>";
        $result .= "<div class='col-lg-6 col-md-6' id='rightForMerge'>";

        foreach ($output as $name => $value) {
            if(!$name){
                continue;
            }
            $result .= "<div class='form-group'><label for='{$name}' class='control-label col-md-6 col-lg-6'>".($setting[$name]['label'] ?? $name)."</label>";

            if (array_key_exists($name, $setting)) {

                $type = $setting[$name]['type'];
                switch ($type) {
                    case 'text':
                        $result .= $this->renderTypeText($value, $name, $setting);
                        break;
                    case 'select':


                        $result .= $this->renderTypeSelect($value, $name, $setting);
//                        if($name === 'bc-user-companies-status-in-business'){
//                            var_dump($value);exit;
//                        }
                        break;
                    case 'tel':
                        $result .= $this->renderTypeTel($value, $name, $setting);
                        break;
                    case 'multiple':

                        $result .= $this->renderTypeMultiple($value, $name, $setting);
                        break;
                    default:
                        $result .= "<input class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
                        break;
                }
            } else {
                $result .= "<input  class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
            }


            $result .= "</div>";
        }
        $result .= "</div>";
        return $result;
    }

    public function renderForAutoSave($data){
        $setting = $this->model->getXlsxMargeColumn();
        $result = [];

        foreach ($data as $name  => $value){
            if(!array_key_exists($name, $setting)){
                $result[$name] = $value;
                continue;
            }
            $type = $setting[$name]['type'];
            if($type == 'select'){

                $valuesDictionary = $setting[$name]['values'];
                if (is_string($setting[$name]['values'])) {
                    $valuesDictionary = getDictionary4Select($setting[$name]['values']);
                }
                $dictionaryId = array_search($value, $valuesDictionary);
                if(!$dictionaryId && $value){
                    //$valuesDictionary = array_merge($valuesDictionary, [$value => $value]);
                    $dictionaryId = $value;

                }
                $value = $dictionaryId;
            }
            if($type == 'multiple'){
                $dictionaryId = null;
                $valuesDictionary = getDictionary4Select($setting[$name]['values']);
                if(is_array($value) && !empty($value)){
                    $dictionaryId = $value;
                }else{
                    $dictionaryId = self::searchValueInDictionary($value, $setting[$name]['values']);
                }
                $value = $dictionaryId;
            }

            $result[$setting[$name]['name']] = $value;
        }
        return $result;
    }

    public function renderFaileds(array $fields)
    {
        $setting = $this->model->getXlsxMargeColumn();
        $result = "";
        foreach ($fields as $name => $value) {
            if(!$name){
                continue;
            }

            $result .= "<div class='form-group col-lg-6 col-md-6'><label for='$name' class='control-label col-md-4 col-lg-4'>".($setting[$name]['label'] ?? $name)."</label>";
            $result .= "<div class='col-lg-6 col-md-6'>";
            if (array_key_exists($name, $setting)) {

                $type = $setting[$name]['type'];
                switch ($type) {
                    case 'text':
                        $result .= $this->renderTypeText($value, $name, $setting);
                        break;
                    case 'select':
                        $result .= $this->renderTypeSelect($value, $name, $setting);
                        break;
                    case 'tel':
                        $result .= $this->renderTypeTel($value, $name, $setting);
                        break;
                    case 'multiple':
                        $result .= $this->renderTypeMultiple($value, $name, $setting);
                        break;
                    case 'disabled':
                        $result .= "<input class='form-control' disabled type='text' value='{$value}' id='$name' name='$name' />";
                        break;
                    default:
                        $result .= "<input class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
                        break;
                }
            } else {
                $result .= "<input class='form-control' type='text' value='{$value}' id='$name' name='$name' />";
            }


            $result .= "</div></div>";
        }
        return $result;
    }




    protected function renderTypeText($value, $name, $setting, $disable = false)
    {


        $result = "<input ". ($disable === true ? 'readonly' : '') ." type='text' class='form-control' value='{$value}' id='$name' data-connect='{$setting[$name]['name']}' name='{$setting[$name]['name']}' />";
        return $result;
    }

    private function renderTypeSelect($value, $name, $setting, $disable = false)
    {
        $result = "<select data-connect='{$setting[$name]['name']}' ".  ($disable === true ? 'data-enable="false" readonly' : '') ." class='form-control select2 ' data-tags='true' name='{$setting[$name]['name']}' id='{$name}'>";
        $dictionaryId = null;
        $valuesDictionary = $setting[$name]['values'];
        $value = trim($value);

        if (is_string($setting[$name]['values'])) {
            $valuesDictionary = getDictionary4Select($setting[$name]['values']);
        }
        $dictionaryId = array_search($value, $valuesDictionary) ?: (self::searchValueInDictionary($value, $valuesDictionary)[0] ?? null) ;

        if(!$dictionaryId && $value){
            if(intval($value) > 100){

                $dictionaryId = $value;
            }else{
                $valuesDictionary = array_merge($valuesDictionary, [$value => $value]);
                $dictionaryId = $value;
            }

        }

        $result .= selectOption($valuesDictionary, $dictionaryId, true);
        $result .= "</select>";
        return $result;
    }

    public function renderTypeTel($value, $name, $setting, $disable = false)
    {
        $result = '';

        $value = array_filter(explode(" ", $value), function ($val) {
            return trim($val);
        });
        $i = 0;
        $connectName = $name;
        $name = $setting[$name]['name'] ?? $name;
        do{
            $phone = $value[$i] ?? '';
            if($phone){
                if($phone[0] !== '+' && strlen($phone) > 10){
                    $phone = "+".$phone;
                }
                if(sizeof($value) > 1){
                    $name = "{$name}_{$i}";
                }
            }
            $result .= '
                <div class="col-md-12 row" style="display: flex">
                    <select class="col-sm-4 col-md-4 form-control" id="'.$name.'_select" name="country_code"  style="width: auto; display: inline;">
       
                    </select>
                        <input data-connect = "'.$setting[$connectName]['name'].'" class="col-sm-8 col-md-8 form-control import-phones" id="'.$name.'"  name="'.$name.'" value="'.$phone.'" pattern="\D[0-9]{9,}" minlength="10" autocomplete="off" type="tel">
                </div>
            ';
            $i++;
        }while($i < sizeof($value));
        return $result;
    }

    public static  function searchValueInDictionary($value, $dictionary, $delimetr = ","){
        if(!$dictionary){
            throw new \RuntimeException("Undefined dictionary value " . gettype($dictionary));
        }
        if(!is_array($dictionary)){
            $dictionary = getDictionary4Select($dictionary);
        }
        if($value){
            $dictionary = array_map(function ($val){
                return  html_entity_decode(htmlspecialchars_decode(trim($val), ENT_QUOTES ));
            }, $dictionary);

            if(!is_array($value)){
                $value = explode($delimetr, $value);
            }

            $value = array_filter($value,function ($val){
                return trim($val);
            });

            $dictionaryId = array_map(function ($val) use ($dictionary){

                return array_search(htmlspecialchars(trim($val)), $dictionary) ?: array_search($val, $dictionary);
            }, $value);
        }else{
            $dictionaryId = array_search($value, $dictionary);
        }

        return $dictionaryId;
    }

    private function renderTypeMultiple($value, $name, $setting, $disable = false)
    {

        $value = is_array($value) ? array_map('trim', $value) : trim($value);

        $result = "<select data-connect='{$setting[$name]['name']}' ". ($disable === true ? 'data-enable="false" disabled' : '') ." value = '' class='form-control select2' multiple name='{$setting[$name]['name']}[]' id='{$name}' tabindex='-1'>";
        $dictionaryId = null;
        $valuesDictionary = getDictionary4Select($setting[$name]['values']);

        if(is_array($value) && !empty($value)){
            $dictionaryId = $value;
        }else{
            $dictionaryId = self::searchValueInDictionary($value, $setting[$name]['values']);
        }

        $result .= selectOption($valuesDictionary, $dictionaryId, true);
        $result .= "</select>";
        return $result;

    }

    public function getButtons()
    {
        return $this->buttons;
    }
}