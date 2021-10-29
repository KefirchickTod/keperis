<?php


namespace src\Core\Page;


use App\Provides\ProvideStructures\bcDictionaryCat;
use PDO;
use src\Core\ActionButton;
use src\Core\Page\Table\ProvideTableContainer;
use src\Core\Table\eeTable;
use src\Http\Request;
use src\Interfaces\Table;
use src\MiddlewareProvideTableTrait;
use src\Structure\ProvideFilter;
use src\Structure\Structure;

class ProvideTable implements Table
{

    use MiddlewareProvideTableTrait;

    const width = 'auto';
    public static $reputatuin = [
        'firstName',
        'secondName',
        'fullName',
    ];
    public static $tfooterValue = [];
    public static $callback;
    public $select2 = "select2_filter";
    /**
     * @var int
     */
    public $fixed = 3;
    public $templates = [
        'attrTable' => 'class = "table table-striped table-responsive-full table-hover table-bordered fist table-sm" style = "font-size: 0.9em; table-layout: fixed; margin-bottom:2px"',
        'thead'     => 'class ="thead-light" nochilddrag="1"', //attr
        'tbody'     => '',
        'tfoot'     => '',
        'th'        => ' scope="col" class="th-table-table" style = " width: {%_width_%};"',
    ];
    public $template = "<div class='tableConver'><div {%_mask_%}>{%_table_%}</div></div>";


    /**
     * @var eeTable
     */
    protected $eeTable;
    private $nameFotGet = [];
    /**
     * @var Structure
     */
    private $structure;
    private $dataArray;
    /**
     * @var \Closure
     */
    private $replace = false;
    /**
     * @var ProvideTableContainer
     */
    private $container;
    private $colors = [];

    function __construct()
    {
        /** @var  $reqyest Request */
        $reqyest = container()->get('request');
        if ($reqyest->isXhr()) {
            $this->template = "<div class='tableConver_ajax'><div {%_mask_%}>{%_table_%}</div></div>";
        } else {
            $this->template = "<div class='tableConver'><div {%_mask_%}>{%_table_%}</div></div>";
        }

        $this->container = new ProvideTableContainer();
        $this->eeTable = new eeTable();

        if (is_null($this->stack)) {
            $this->seedMiddlewareStack(function ($data) {

                return $data;
            });
        }

    }

    public function setColor(int $index, string $color)
    {
        $this->colors[$index] = $color;
    }

    public function withContainer(ProvideTableContainer $container)
    {
        $clone = clone $this;
        $clone->container = $container;
        return $clone;
    }

    /**
     * @return ProvideTableContainer
     */
    public function container()
    {
        return $this->container;
    }

    public function setting(array $setting = [], array $title = [], $filter = [], array $action = [])
    {

        $this->container->execute($setting, $title, $filter, $action);
        $this->templates = $this->container->setting ? array_replace_recursive($this->templates,
            $this->container->setting) : $this->templates;
    }

    public function callbackRow(\Closure $closure)
    {
        $this->container->bindCallbackRow($closure, $this);
        return $this;
    }

    public function setData(Structure $structure, array $dataArray)
    {
        $this->structure = $structure;
        $this->dataArray = $dataArray;

        if (!$this->container->getCallbackRow()) {
            $this->container->callbackRow(PageCreator::$row_init);
            if (self::$callback instanceof \Closure) {
                $this->container->bindCallbackRow(self::$callback, $this);
            }
        }


        $this->container->setRow($this->getRow());
    }

    public function getRow()
    {
        if ($this->container->getRow()) {
            return $this->container->getRow();
        }

        $this->structure->set($this->dataArray);
        $key = key($this->dataArray);
        if ($this->container->getCallbackRow()) {
            return $this->structure
                ->getData($this->container->getCallbackRow(), $key);
        }

        return $this->structure->get($key);


    }

    public function withColumn($key)// using
    {
        if ($this->container->getRow()) {
            return array_column($this->container->getRow(), $key);
        }
        return [];
    }

    public function render(): string
    {
        $this->parse();

        if ($this->replace == false) {
            $this->tablePattern([], '');

        }


        return preg_replace("/{%_table_%}/", $this->eeTable->__toString(), $this->template);
    }

    private function parse(array $get = []): void
    {

        if (valid($this->templates, 'attrTable', null)) {
            $this->eeTable->setAttr($this->templates['attrTable']);
        }

        $get = $get ?: ['thead', 'addFilter', 'tbody', 'tfoot'];

        $this->eeTable->setTBodyAttr(valid($this->templates, 'tbody', ''));
        $this->eeTable->setTheadAttr(valid($this->templates, 'thead', ''));
        $this->eeTable->setTFootAttr(valid($this->templates, 'tfoot', ''));
        foreach ($get as $func) {
            $data = call_user_func([$this, $func]);

            if ($data) {
                $as = 'setAs' . ucfirst($func == 'addFilter' ? 'tbody' : $func);
                $this->eeTable->addRow($this->eeTable->newRow()->addArrayOfCells($data)->$as());
            }

        }
    }

    public function tablePattern(array $replaces, string $line)
    {
        if (!$replaces) {
            $replaces = [
                '{%_loadbar_%}' => '',
                "{%_mask_%}"    => 'class = "dragscroll scroll-user-table table-multi-columns  min-height55" data-fixed = "' . $this->fixed . '" style="cursor: grab; overflow: scroll auto; min-height: 100%;"',
            ];
        }

        $this->template = $line && $line != '' ? $line : $this->template;
        $this->replace = true;
        foreach (array_keys($replaces) as $value) {
            $this->template = preg_replace("/" . $value . "/", $replaces[$value], $this->template);
        }
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->eeTable->__toString();
    }

    public function add(callable $callable)
    {
        $this->addMiddleware($callable);
        return $this;
    }

    public function setRow(array $row)
    {

        $this->container->setRow($row);
        return $this;
    }

    /**
     * @return array
     */
    protected function addFilter()
    {


        if (!$this->container->filter || container()->request->isXhr()) {
            return [];
        }

        $result = [];


        $filter = $this->container->filter instanceof ProvideFilter ?
            $this->container->filter->setStrucutre($this->structure ?: \structure())->setTitle($this->container->title)->getFilter() :
            array_values($this->container->filter);

        $keys = array_keys($this->container->title);
        array_unshift($filter, false);

        foreach ($keys as $key) {
            if (array_key_exists($key, $filter)) {
                $result[] = $this->eeTable->newCell()->addData(

                    preg_match('~option~', $filter[$key]) ? html()
                        ->select([
                            'id'       => "select_$key",
                            'multiple' => '',
                            'class'    => $this->select2,
                            'data-key' => $key
                        ])->insert($filter[$key])
                        ->end('select')
                        ->input([
                            'id'    => "$key",
                            'type'  => 'hidden',
                            'value' => "no",
                        ]) : $filter[$key]
                )
                    ->addClass('ui-state-default')
                    ->setAttr('rowspan = 1 colspan = 1');
            } else {
                $result[] = $this->eeTable->newCell()->addData(' ')->addClass('ui-state-default')->setAttr('rowspan = 1 colspan = 1');;
            }
        }
        $this->container->setFilter($filter);


        return $result ?: [];

    }


    /**
     * @return array
     */
    protected function tbody()
    {
        $keys = array_keys($this->container->title);
        $result = [];

        $row = $this->container->getRow();


        $size = count($row);


        $names = $this->getUserNames();


        $rowKeys = array_keys($row);
        foreach ($rowKeys as $counting) {
            $value = $row[$counting] ?? null;
            if (!$value || !is_array($value)) {
                continue;
            }

            $value = $this->callMiddlewareStack($value, $this->container->title);
            foreach ($keys as $key) {




                if (!array_key_exists($key, $value)) {
                    //var_dump($value);
                    //debug($key, $this->container->action);
                    if ($key === 'event_o' && $this->container->action) {

                        $this->container->setAction(isset($this->container->action[0]) ? $this->container->action[0] : $this->container->action);
                        $event = (new ActionButton($value, $this->container->action))->__toString();
                        $result[] = $this->eeTable->newCell()->addData("<div class = 'action-icon'>$event</div>")->setAttr("data-label = '" . addslashes($this->container->title[$key]['text']) . "'");;

                    } elseif (isset($this->container->title[$key]['dynamic'])) {


                        $setting = $this->container->title[$key]['dynamic'];
                        $data = $this->dynamicFilter($setting, ($value[$key] ?? null), $value['id']);
                        $result[] = $this->eeTable->newCell()->addData("<div class='tr-content' style = ' -webkit-line-clamp: 8;' >{$data}</div")->setAttr("data-label = '" . addslashes($this->container->title[$key]['text']) . "'");

                    } else {
                        $result[] = $this->eeTable->newCell()->addData("");

                    }
                    continue;
                }


                $note = $this->inNote($key);


                $value[$key] = $this->renderUserName($key, $value, $names);


                $value[$key] = $this->renderRedirect($value, $key);

                $value[$key] = $this->renderLink($key, $value);

                $reputation = $this->renderReputatuin($value, $key);


                if ($reputation !== false) {
                    $result[] = $reputation;
                } else {

                    $line = valid($this->container->title[$key], 'line', '8');
                    if (isset($this->container->title[$key]['dynamic'])) {

                        $setting = $this->container->title[$key]['dynamic'];
                        $data = $this->dynamicFilter($setting, $value[$key], $value['id']);
                        $result[] = $this->eeTable->newCell()->addData("<div class='tr-content'  data-id = '{$value['id']}' style = ' -webkit-line-clamp: {$line};' >{$data}</div")->setAttr("data-label = '" . addslashes($this->container->title[$key]['text']) . "'");
                    } else {
                        if ($note) {

                            $result[] = $this->eeTable->newCell()->addData("<div class='tr-content createNote' data-o = '{$note['o']}'  data-id = '{$value['id']}' style = ' -webkit-line-clamp: {$line};' title = '" . clean($value[$key]) . "'>{$value[$key]}</div")->setAttr("data-label = '" . addslashes($this->container->title[$key]['text']) . "'");
                        } else {


                            if($value[$key]){
                                $value[$key] = isset($this->container->title[$key]['date_format']) ? date($this->container->title[$key]['date_format'],
                                    strtotime($value[$key])) : $value[$key];
                            }
                            $result[] = $this->eeTable->newCell()->addData("<div class='tr-content '  data-id = '" . ($value['id'] ?? $key) . "' style = ' -webkit-line-clamp: {$line};' title = '" . htmlspecialchars_decode(clean($value[$key])) . "'>{$value[$key]}</div")->setAttr("data-label = '" . addslashes($this->container->title[$key]['text'] ?? '') . "'");
//
                        }
                    }


                }
            }


            $this->eeTable->setTBodyAttr($this->templates['tbody'])->addRow($this->eeTable->newRow()->setAttr("style = '" . $this->getColorAsStyle(intval($value['id'] ?? 0)) . "' data-table-row-id = '" . ($value['id'] ?? -1) . "' data-table-count = '{$counting}'")->addArrayOfCells($result ?: [])->setAsTbody());
            $result = [];

        }
        return [];
    }

    private function getUserNames()
    {


        if (!$this->nameFotGet) {
            return null;
        }

        $row = $this->container->getRow();
        $ids = [];
        foreach ($this->nameFotGet as $column) {

            $ids = array_merge($ids, array_filter(array_column($row, $column), function ($var) {
                return intval($var) > 0;
            }));

        }


        $ids = array_unique($ids);
        if (!$ids) {
            return [];
        }

        if (is_string($ids[0]) && intval($ids[0]) < 2) {
            return [];
        }

        return getUserNameArray($ids);
    }

    /**
     * @param array $setting
     * @return string
     * @example $setting = [
     *  'id' => 1,
     * ]
     */
    private function dynamicFilter($setting, $currentValue, $registerId)
    {

        $id = $setting['id'];
        $key = $setting['key'];
        $o = $setting['o'] ?? 'updateRegisterTagsStatus';
        $data = db()->querySql("SELECT bc_connections_db_right_id, bc_connections_db_id FROM bc_connections_db WHERE bc_connections_db_right_key = '$key' AND bc_connections_db_left_id = {$id} GROUP BY bc_connections_db_right_id")->fetchAll(PDO::FETCH_ASSOC);
        $data_copy = $data;
        $data = array_column($data, 'bc_connections_db_right_id');
        $result = [];
        if ($data) {
            $data = array_diff($data, ['0', null, false, '']);
            $values = \structure()->set([
                'dynamic' . $key =>
                    [
                        'get'     => ['b_titleUK'],
                        'class'   => bcDictionaryCat::class,
                        'setting' =>
                            [
                                'where' => 'id IN (' . join(', ', $data) . ')',
                            ],
                    ],
            ])->get('dynamic' . $key);

            foreach ($values as $key => $val) {
                $id = $data_copy[$key]['bc_connections_db_id'];
                if ($currentValue == $val['id']) {

                    $result[0] = "<span class='toUperCase'>{$val['b_titleUK']}</span>";
                } else {

                    $result[$id] = "<a href='#' class='updateStatus' data-o = '$o' data-id = '$registerId' data-status = '{$val['id']}'>{$val['b_titleUK']}|</a>";
                }
            }
            if (isset($result[0])) {

                $result[] = "<a href='#' class='updateStatus' data-o = '$o'  data-status = '0' data-id = '$registerId'>скинути |</a>";
            }
            if ($result) {
                ksort($result);
            }
//            if($result){
//                uksort($result, function ($a, $b){
//                    if($a === 'main' || $a === $b){
//                        return 1;
//                    }
//                    return -1;
//                });
//                $result = array_reverse($result);
//            }
            return "<div class = 'dynamicFilters'>" . join('', $result) . "</div>";
        }


        return false;
    }

    private function inNote($key)
    {
        if (isset($this->container->title[$key]['note'])) {
            return $this->container->title[$key]['note'];
        }
        return false;
    }

    private function renderUserName($key, $value, $names)
    {


        if (!isset($names[$value[$key]])) {

            //var_dump($this->nameFotGet);exit;
            if (in_array($key, $this->nameFotGet)) {

                return !boolval($value[$key]) ? '' : $value[$key];
            }

            return $value[$key];
        }


        $link = $this->container->title[$key]['link'] ?? null;
        $name = $names[$value[$key]];
        if ($link) {
            $name = html()->a([
                'text'   => $name,
                'target' => '_blank',
                'href'   => preg_replace("/%_value_%/", $value[$key], $this->container->title[$key]['link']),
            ])->render(true);

        }
        return $name;


    }

    private function renderRedirect($value, $key)
    {
        if (!isset($this->container->title[$key]['userId'])) {
            return $value[$key];
        }

        $user = $value['userId'] ?? $value['bc_user_id'] ?? null;

        if ($user) {
            return html()->a([
                'text'   => $value[$key],
                'target' => "_blank",
                'href'   => route('user.info', ['id' => $user]),
            ])->render(true);
        }

        return $value[$key];
    }

    private function renderLink($key, $value)
    {

        if (!array_key_exists($key, $this->container->title) ||
            !array_key_exists('link', $this->container->title[$key])) {
            return $value[$key];
        }
        if (!is_array($this->container->title[$key]['link'])) {
            $href = $this->container->title[$key]['link'];
            $need = $key;
        } else {
            $href = $this->container->title[$key]['link']['href'];
            $need = $this->container->title[$key]['link']['value'];

        }

        if (!array_key_exists($need, $value)) {
            return $value[$key];
        }

        $href = preg_replace("/%_value_%/", $value[$need], $href);
        $value[$key] = html()->a(['text' => $value[$key], 'href' => $href, 'target' => '_blank'])->render(true);
        return $value[$key];
    }

    private function renderReputatuin($value, $key)
    {
        if (!in_array($key, self::$reputatuin)) {
            return false;
        }
        $reputation = $value['reputation'] ?? '';
        if ($reputation === 'Негативна') {
            return $this->eeTable->newCell()->addData($value[$key])->setAttr("style = 'color:red' data-label = '" . addslashes($this->container->title[$key]['text']) . "'");
        }
        return false;


    }

    public function getColorAsStyle($index)
    {
        if (!$this->getColor($index)) {
            return "";
        }
        $style = "background-color: " . $this->getColor($index) . ";";
        return $style;
    }

    public function getColor(int $index)
    {
        return $this->colors[$index] ?? "";
    }

    /**
     * @return array
     */
    protected function thead()
    {
        $result = [];

        foreach ($this->container->title as $name => $value) {
            if (isset($value['name']) && $value['name'] == true) {
                $this->nameFotGet[] = $name;
            }
            $width = isset($value['width']) ? $value['width'] : self::width;
            if (isset($value['sort']) && $value['sort'] == true) {

                $attr = preg_replace('/{%_width_%}/', $width, $this->templates['th']);
                $nameOfValue = (get('sort') == $name) ? "a_" . $name : $name;
                $sortClass = $this->getSortClass($name, $nameOfValue);

                $result[] = $this->eeTable->newCell()->setAttr($attr)
                    ->addData(html()->div('style = "cursor:pointer;" class="sort-icon-box" data-sort = "' . $nameOfValue . '"')
                        ->span()
                        ->insert($value['text'])
                        ->end('span')
                        ->i([
                            'class' => $sortClass,
                        ])->end('div')->end('i')->div(['class' => 'col-resize'])->div(['class' => 'draggingLine'])->end('div')->render(true)
                    );
            } else {
                $width = $name == 'event_o' ? '55px' : $width;
                $attr = preg_replace('/{%_width_%}/', $width, $this->templates['th']);
                $result[] = $this->eeTable->newCell()->setAttr($attr)->addData("<span style='text-align: center; width: 100%; display: block'>" . (isset($value['text']) ? $value['text'] : 'Дії') . "</span>");
            }
        }
        return $result;
    }

    /**
     * return name of fa fa-sort desc or asc
     * @param $nameForSort
     * @param $nameOfValue
     * @return string
     */
    private function getSortClass($nameForSort, $nameOfValue)
    {
        if (get('sort')) {
            if (get('sort') === $nameForSort) {
                $sortClass = 'fa fa-sort-desc';
            } else {
                if (preg_match("~a_~", get('sort')) &&
                    explode('_', get('sort'))[1] == $nameOfValue
                ) {
                    $sortClass = 'fa fa-sort-asc';
                } else {
                    $sortClass = 'fa fa-sort';
                }
            }
        } else {
            $sortClass = 'fa fa-sort';
        }
        return $sortClass;
    }

    /**
     * @return array
     */
    protected function tfoot()
    {
        if (self::$tfooterValue) {
            $result = [];
            $keys = array_keys($this->container->title);
            foreach (self::$tfooterValue as $value) {
                foreach ($keys as $key) {
                    $result[] = $this->eeTable->newCell()->addData($value[$key] ?? '');
                }
                $this->eeTable->setTBodyAttr($this->templates['tfoot'])->addRow($this->eeTable->newRow()->addArrayOfCells($result ?: [])->setAsTfoot());
                $result = [];
            }

            self::$tfooterValue = [];
        }
        return [];
    }


}
