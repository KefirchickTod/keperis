<?php


namespace src\Core\Page;


use src\Core\ActionButton;
use src\Core\Table\eeTable;
use src\Http\Request;
use src\Interfaces\Table;
use src\Structure\ProvideFilter;
use src\Structure\Structure;

class ProvideTable implements Table
{

    const width = 'auto';

    public $select2 = "select2_filter";

    public static $tfooterValue = [];
    /**
     * @var int
     */
    public $fixed = 3;

    public $templates = [
        'attrTable' => 'class = "table table-striped table-responsive-full table-hover table-bordered fist table-sm" style = "font-size: 0.9em; table-layout: fixed; margin-bottom:2px"',
        'thead' => 'class ="thead-light"', //attr
        'tbody' => '',
        'tfoot' => '',
        'th' => ' scope="col" style = "position: relative; vertical-align: middle; text-align:center; width: {%_width_%};"',
    ];
    public $template = "<div class='tableConver'><div {%_mask_%}>{%_table_%}</div></div>";
    /**
     * @var array
     */
    protected $event;
    /**
     * @var ProvideFilter|array
     */
    protected $filter;
    /**
     * @var array
     */
    protected $title;
    /**
     * @var eeTable
     */
    protected $eeTable;
    private $row;
    private $nameFotGet = [];
    private $structure;
    private $dataArray;
    private $replace = false;
    /**
     * @var \Closure
     */
    private $row_init;


    function __construct()
    {
        /** @var  $reqyest Request */
        $reqyest = container()->get('request');
        if ($reqyest->isXhr()) {
            $this->template = "<div class='tableConver_ajax'><div {%_mask_%}>{%_table_%}</div></div>";
        } else {
            $this->template = "<div class='tableConver'><div {%_mask_%}>{%_table_%}</div></div>";

        }
    }

    public function setting(array $setting = [], array $title = [], $filter = [], array $action = [])
    {
        $this->eeTable = new eeTable();
        $this->templates = $setting ? array_replace_recursive($this->templates, $setting) : $this->templates;
        $this->filter = $filter;
        $this->event = $action === null ? [] : $action;
        $this->title = $title;
    }

    public function setAction($action)
    {
        $this->event = $action;
        return $this;
    }

    public function setTitle(array $title)
    {
        $this->title = $title;
        return $this;
    }


    public function callbackRow(\Closure $closure)
    {
        $this->row_init = $closure;
        return $this;
    }


    public function setData(Structure $structure, array $dataArray)
    {
        $this->structure = $structure;
        $this->dataArray = $dataArray;

        $this->row_init = $this->row_init ?: PageCreator::$row_init;


        if ($this->row_init) {
            $this->row = $this->structure->set($dataArray)->getData($this->row_init, key($dataArray));
        } else {
            $this->row = $this->structure->set($dataArray)->get(key($dataArray));
        }
        // TODO: Implement setData() method.
    }

    public function withColumn($key)// using
    {
        if ($this->row && valid($this->row[0], $key)) {
            return array_column($this->row, $key);
        }
        return null;
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
                "{%_mask_%}" => 'class = "dragscroll scroll-user-table table-multi-columns  min-height55" data-fixed = "' . $this->fixed . '" style="cursor: grab; overflow: scroll auto; min-height: 100%;"',
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

    public function getRow()
    {
        return $this->row;
    }

    public function setRow(array $row)
    {
        $this->row = $row;
        return $this;
    }

    /**
     * @return array
     */
    protected function addFilter()
    {
        $result = [];
        if ($this->filter) {

            if ($this->filter instanceof ProvideFilter) {
                $structure = !($this->structure instanceof Structure) ? \structure() : $this->structure;
                $this->filter = $this->filter->setStrucutre($structure)->setTitle($this->title)->getFilter();
            } else {
                $this->filter = array_values($this->filter);
            }
            $keys = !$this->filter ? [] : array_keys($this->title);
            array_unshift($this->filter, false);
            foreach ($keys as $id => $name) {

                $name = isset($this->filter[$name]) ? $name : $id;
                if (isset($this->filter[$name]) && $this->filter[$name] != false) {
                    $result[] = $this->eeTable->newCell()->addData(

                        preg_match('~option~', $this->filter[$name]) ? html()
                            ->select([
                                'id' => "select_$name",
                                'multiple' => '',
                                'class' => $this->select2,
                            ])->insert($this->filter[$name])
                            ->end('select')
                            ->input([
                                'id' => "$name",
                                'type' => 'hidden',
                                'value' => "no",
                            ]) : $this->filter[$name]
                    )
                        ->addClass('ui-state-default')
                        ->setAttr('rowspan = 1 colspan = 1');
                } else {
                    $result[] = $this->eeTable->newCell()->addData(' ');
                }
            }
            self::jsCreateFilterInclude($this->title);
        }
        return $result;

    }

    public static function jsCreateFilterInclude($title)
    {
        if (isset($title['id'])) {
            unset($title['id']);
        }
        $title = array_keys($title);

        // var_dump($title);
        $toJS = isset($_GET['filter']) ? $_GET['filter'] : '';
        $lenght = isset($_GET['length']) ? $_GET['length'] : '10';
        if (PageCreator::$script == true) {
            ?>
            <script>
              try {
                var title = <?= json_encode($title) ?>;
              }
              catch (e) {
                console.log('');
              }

              //
              //function filterSend() {
              //
              //    let saveForGet = {};
              //    let getQuery = new QueryGet();
              //    for (var i = 0; i < title.length; i++) {
              //        //console.log(title[i]);
              //        if (document.getElementById(title[i]) != null) {
              //
              //            let value = document.getElementById(title[i]).value;
              //            if (typeof value == 'undefined' || !value || value.length === 0 || value === "" || !/[^\s]/.test(value) || /^\s*$/.test(value) || value.replace(/\s/g, "") === "") {
              //                continue;
              //            }
              //            let arrayValue = value.split(',');
              //            arrayValue = arrayValue.filter(function (el) {
              //                return encodeURIComponent(el) != null;
              //            });
              //
              //            if (arrayValue !== 'no') {
              //                saveForGet[title[i]] = encodeURIComponent(arrayValue);
              //            }
              //        }
              //
              //
              //    }
              //
              //    sessionStorage.scrollLeft = $(".double-scroll").scrollLeft();
              //    getQuery.data.filter = JSON.stringify(clean(saveForGet, 'no'));
              //    if (saveForGet !== '') {
              //
              //        historyPushJson(getQuery.data);
              //        updateTable();
              //        //if (getQuery.data.search) {
              //        //    creat
              //        //    window.location.href = '?filter=' + JSON.stringify(saveForGet) + '&' + saveForGet + '&length=' + '<?////=$lenght?>////' + '&search=' + getQuery.data.search;
              //        //} else {
              //        //
              //        //    window.location.href = '?filter=' + JSON.stringify(saveForGet) + '&' + saveForGet + '&length=' + '<?////=$lenght?>////';
              //        //}
              //    }
              //    //
              //    //if (getQuery.data.search) {
              //    //    window.location.href = '?filter=' + JSON.stringify(saveForGet) + '&length=' + '<?////=$lenght?>////' + '&search=' + getQuery.data.search;
              //    //} else {
              //    //
              //    //    window.location.href = '?filter=' + JSON.stringify(saveForGet) + '&length=' + '<?////=$lenght?>////';
              //    //}
              //
              //}

            </script>
            <?php
        }
    }

    /**
     * @return array
     */
    protected function tbody()
    {
        $keys = array_keys($this->title);
        $result = [];


        foreach ($this->row as $counting => $value) {

            foreach ($keys as $key) {
                if (isset($value[$key])) {
                    $note = ($this->title[$key]['note'] ?? null);

                    $line = valid($this->title[$key], 'line', '8');
                    if ($note) {
                        $result[] = $this->eeTable->newCell()->addData("<div class='tr-content createNote' data-o = '{$note['o']}'  data-id = '{$value['id']}' style = ' -webkit-line-clamp: {$line};' title = '" . clean($value[$key]) . "'>{$value[$key]}</div")->setAttr("data-label = '" . addslashes($this->title[$key]['text']) . "'");
                    } else {
                        $value[$key] = isset($this->title[$key]['date_format']) ? date($this->title[$key]['date_format'],
                            strtotime($value[$key])) : $value[$key];

                        $result[] = $this->eeTable->newCell()->addData("<div class='tr-content '  data-id = '{$value['id']}' style = ' -webkit-line-clamp: {$line};' title = '" . htmlspecialchars_decode(clean($value[$key])) . "'>{$value[$key]}</div")->setAttr("data-label = '" . addslashes($this->title[$key]['text'] ?? '') . "'");
//                                if(error_get_last() && error_get_last()['type'] === E_NOTICE){
//                                    var_dump(error_get_last());
//                                    debug($key, $this->title, $this->title[$key]['text']);
//                                }
                    }


                } else {
                    if ($key === 'event_o' && isset($this->event) && $this->event) {

                        $this->event = isset($this->event[0]) ? $this->event[0] : $this->event;
                        $event = (new ActionButton($value, $this->event))->__toString();
                        $result[] = $this->eeTable->newCell()->addData("<div class = 'action-icon'>$event</div>")->setAttr("data-label = '" . addslashes($this->title[$key]['text']) . "'");;

                    } else {
                        $result[] = $this->eeTable->newCell()->addData("");
                    }
                }
            }

            $this->eeTable->setTBodyAttr($this->templates['tbody'])->addRow($this->eeTable->newRow()->setAttr("data-table-row-id = '" . ($value['id'] ?? -1) . "' data-table-count = '{$counting}'")->addArrayOfCells($result ?: [])->setAsTbody());
            $result = [];
            gc_collect_cycles();
        }
        return [];
    }


    /**
     * @return array
     */
    protected function thead()
    {
        $result = [];
        foreach ($this->title as $name => $value) {
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
                        ])->end('div')->render(true)
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
            $keys = array_keys($this->title);
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