<?php


namespace src\Core\Page;


use src\Core\easyCreateHTML;
use src\Http\Request;
use src\Http\Uri;
use src\Interfaces\Paginator;
use src\Structure\ProvideRegister;
use src\Structure\ProvideSettingParse;
use src\Structure\Structure;
use Error;

class PageCreatePaginator implements Paginator
{

    const up = 2;
    public static $distinct = [];
    public static $limit;
    public static $length = 10;
    public $currentPage;
    public $setting = [];
    private $name;
    private $template = null;
    /**
     * @var Structure
     */
    private $structure;
    private $size;
    private $dataArray;
    /**
     * @var int
     */
    private $totalPage;

    function __construct()
    {
        $this->uri = container()->get('request')->getUri();
        $this->currentPage = valid(\uri()->getParseQuery(), 'page', 1);


        $filter = valid(\uri()->getParseQuery(), 'filter', null);
        if ($filter) {
            $filter = is_string($filter) ? json_decode($filter, true) : $filter;
            self::$distinct = array_keys($filter);
        }


    }

    public static function limit($page = 1, $length = 10)
    {
        if ($length <= 0) {
            $length = 10;
        }

        $page = $page - 1;


        self::$limit = $length > 0 ? ((string)$page * $length) . ", $length" : "";
        self::$length = $length;
        return self::$limit;
    }

    public function setSetting(array $setting): void
    {
        $this->setting = $setting;
    }

    public function templates(string $template)
    {
        $this->template = $template;
    }

    public function setData(Structure $structure, array $dataArray)
    {
        $this->structure = $structure;
        $this->dataArray = $dataArray;
        $this->name = key($dataArray);
    }

    public function render(): string
    {
        $result = "<div class='paginatorInfo'><div class='dynamicPaginator row'>";

        $methods = [$this->parse(), $this->posts()];
        $methods = array_reverse($methods);
        foreach ($methods as $method) {
            $result .= "<div class='col-lg-" . ceil(12 / count($methods)) . "'>" . $method . "</div>";
        }
        $result .= "</div></div>";
        return $result;
    }

    public function parse(array $setting = null)
    {
        $setting = $this->setting ?: $setting;
        $this->size = $this->size();
        if ($this->size / self::$length < 1) {
            return '';
        }
        $this->totalPage = ceil($this->size / self::$length);
        $this->template = $this->template ?: $this->defaultTemplate();
        $setting['{%_size_%}'] = $this->totalPage;
        $this->template = preg_replace('/{%_link_%}/', $this->totalPage, $this->template);
        $setting['{%_counter_%}'] = $this->counter($setting['{%_counter_%}'], $setting);
        $pattern = ['/{%_counter_%}/', '/{%_styleForLast_%}/', '/{%_size_%}/'];

        if (isset($setting['{%_first_%}'])) {
            $pattern = array_merge([0 => '/{%_first_%}/'], $pattern);

        } else {
            $this->template = preg_replace("/{%_first_%}/", '', $this->template);
        }

        $this->template = preg_replace($pattern, $setting, $this->template);
        return $this->template;
    }

    public function size(): int
    {
        //$filter = isset($_GET['filter']) ? json_decode($_GET['filter'], 1) : [];
        $exception = $this->exception();
        $data = $this->dataArray;
        $pattern = [];
        $pattern[$this->name] = $this->structure->classes($this->name)->getPattern();
        $parser = new ProvideSettingParse();
        $reverseGet = is_array($data[$this->name]['get']) ? array_flip($data[$this->name]['get']) : [$data[$this->name]['get'] => ''];

        if (isset($this->structure->setting[$this->name]['join']) && isset($data[$this->name]['setting'])) {

            foreach ($this->structure->setting[$this->name]['join'] as $name => $value) {
                if (isset($value['distinct']) && $value['distinct'] == true) {
                    continue;
                }
                $value['get'] = !is_array($value['get']) ? [$value['get']] : $value['get'];

                $intersect = is_array($exception) ? array_intersect($value['get'], $exception) : null;

                if (!empty($intersect)) {
                    $data[$this->name]['setting']['join'][$name]['get'] = $intersect;
                    continue;
                }
                $pattern[$name] = $this->structure->classes($name)->getPattern();
                unset($pattern[$name]['id']);
                try {
                    foreach ($data[$this->name]['setting'] as $nameSetting => $settingValue) {
                        if (!is_array($settingValue)) {
                            $parsed = $parser->setPatterns($pattern[$name])->parsePattern($settingValue, false);
                            if ($parsed == $settingValue && preg_match("/$parsed/", $settingValue)) {
                                unset($data[$this->name]['setting']['join'][$name]);

                            }
                        }
                    }
                } catch (\Error $exception) {
                    var_dump($exception->getMessage());
                    continue;
                }
            }
            if (empty($data[$this->name]['setting']['join'])) {
                unset($data[$this->name]['setting']['join']);
            }
        }

        if (isset($data[$this->name]['setting'])) {
            $parser->setPatterns($this->structure->classes($this->name)->getPattern());
            foreach ($data[$this->name]['setting'] as $name => $value) {
                if (!is_array($value)) {
                    $parser->parsePattern($value);
                }
            }
        }

        $noUnset = $parser->getSaveInput();
        $noUnset = array_unique(array_merge($noUnset, $exception, self::$distinct));

        if ($noUnset) {
            foreach ($reverseGet as $name => $value) {
                if (!in_array($name, $noUnset) && count($reverseGet) > 1) {
                    unset($reverseGet[$name]);
                }
            }
        }

        if (isset($data[$this->name]['setting']['group'])) {
            unset($data[$this->name]['setting']['group']);
        }
        if (isset($data[$this->name]['setting']['limit'])) {
            unset($data[$this->name]['setting']['limit']);
        }
        if (isset($data[$this->name]['setting']['order'])) {
            unset($data[$this->name]['setting']['order']);
        }
        $data[$this->name]['get'] = array_merge(array_flip($reverseGet), ['size']);
        $size = new Structure();

        ProvideRegister::removeData($this->name);
        $size->bindClouser();

        $size = $size->set($data)->getData(function ($row) {
            if (isset($row[0]) && isset($row[0]['size'])) {
                return intval($row[0]['size']);
            }
            //debug($row);

            if ($this->isError()) {
                $size = db()->selectSqlPrepared("SELECT FOUND_ROWS () AS size");
                return $size[0]['size'] ?? 1;

            }
            return isset($row[0]) && isset($row[0]['size']) ? (int)$row[0]['size'] : 1;
        }, $this->name);


        // debug($size);
        return $size;
    }

    /**
     * @param null $get
     * @return array
     */
    public function exception($get = null)
    {
        $result = [];
        if (isset($this->dataArray[$this->name]['setting']['where'])) {
            $where = $this->dataArray[$this->name]['setting']['where'];
            $where = preg_split("/(AND|OR|LIKE|<>|<|>|=)/", $where);
            foreach ($where as $value) {
                $selector = $this->structure->findBySelector(trim($value));
                if (is_array($selector) && $selector) {
                    foreach ($selector as $key) {
                        $result[] = $key;
                    }
                }
            }
            $result = array_diff($result, ['', null]);
        }
        return array_unique(array_merge(self::$distinct, $result));
    }

    private function defaultTemplate(): string
    {
        return html()->div(['class' => 'zui-pager'])
            ->ol(['class' => 'btn-group'])
            ->li('class = "btn-group__item""')
            ->i('class="i-chevron-left paginator-fetch" data-page ="' . ($this->currentPage > 1 ?
                    ($this->currentPage - 1) : $this->currentPage) . '"')->end('i')
            ->end('li')
            ->insert("{%_first_%}")
            ->li('class = "btn-group__item""')
            ->button([
                'class' => 'btn btn--basic',
                'disabled' => '',
                'style' => 'display:' . ($this->currentPage > self::up ? 'block' : 'none'),
            ])
            ->end('li')
            ->insert('{%_counter_%}')
            ->li('class="btn-group__item"')
            ->button(['class' => 'btn btn--basic', 'disabled' => '', 'style' => '{%_styleForLast_%}'])->end('button')
            ->end('li')
            ->li('class="btn-group__item"')
            ->button([
                'class' => 'btn btn--basic',
                'data-page' => '{%_link_%}',

                'text' => '{%_size_%}',
            ])->end('button')
            ->end('li')
            ->end('ol')
            ->form(['method' => 'get', 'class' => 'zui-pager__input'])
            ->input(['type' => 'text', 'name' => 'page'])
            ->end('form')
            ->end('div')->render(true);
    }

    private function counter($counterTemplate, &$setting): string
    {
        $result = [];

        $totalPages = $this->totalPage;
//        $counterTemplate = preg_replace("{%_current_%}", $setting['{%_current_%}'], $counterTemplate);
//        unset($setting['{%_current_%}']);
        $from = $this->currentPage;
        $setting = $this->currentPage > self::up ? [
                "{%_first_%}" => preg_replace(['/{%_value_%}/', '/{%_link_%}/'], [1, newGenerateLink('page', 1)],
                    $counterTemplate),
            ] + $setting : $setting;

        if ($this->currentPage > (self::up + 1) || ($this->currentPage + self::up) < $this->totalPage) {
            $from = $this->currentPage - self::up;
        }
        foreach (range(($from > 0 ? $from : 1),
            (($this->currentPage + self::up) < $this->totalPage ? ($this->currentPage + self::up) : $this->totalPage)) as $page) {
            if ($totalPages >= $this->size) {
                break;
            }
            $result[$page] = preg_replace(['/{%_value_%}/', '/{%_link_%}/'], [$page, newGenerateLink('page', $page)],
                $counterTemplate);
        }
        $current = $this->currentPage;
        $result = array_map(function ($key, $value) use ($setting, $current) {
            if ($current == $key) {
                return preg_replace("/{%_current_%}/", $setting['{%_current_%}'], $value);
            } else {
                return preg_replace("/{%_current_%}/", '', $value);
            }
        }, array_flip($result), $result);
        unset($setting['{%_current_%}']);
        return join('', $result);

    }

    public function posts()
    {
        $from = $this->currentPage * self::$length + 1 - self::$length;
        $to = $this->currentPage * self::$length;
        $result = "<div class='dataTables_info'>";
        if ($to >= $this->size) {
            $result .= 'Записи з ' . $from . " <span id ='rows_to'> по {$this->size} </span> з записів {$this->size}";;
        } else {
            $result .= "Записи з {$from} по <span id = 'rows_to'>{$to}</span> з {$this->size} записів";
        }
        $result .= "</div>";
        return $result;
    }
}