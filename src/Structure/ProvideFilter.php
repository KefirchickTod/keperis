<?php


namespace src\Structure;


use src\Core\easyCreateHTML;
use src\Interfaces\ProvideFilterInterface;
use App\Models\Dictionary\DictionaryModel;


class ProvideFilter implements ProvideFilterInterface
{

    private $select = [];
    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $title;
    /**
     * @var Structure
     */
    private $structure;

    function __construct($structure = null)
    {
        $this->template = "<option value='empty'>пусті</option><option value='filled'>заповнені</option>{%_value_%}";
        if ($structure && $structure instanceof Structure) {
            $this->structure = $structure;
        }
    }

    public function setStrucutre(Structure $structure)
    {
        $this->structure = $structure;
        return $this;
    }

    public function setTitle(array $title)
    {
        $this->title = $title;
        return $this;
    }

    public function getFilter(): array
    {
        return $this->parse()->select;
    }

    private function findClassOfStructure($searchValue){

        foreach ($this->structure->get as $class => $get){
            if(in_array($searchValue, $get)){
                return get_class($this->structure->classes($class));
            }
        }
        return null;
    }

    private function parse()
    {


        foreach ($this->title as $title => $value) {
            if (array_key_exists('filter', $value)) {

                $class = $this->findClassOfStructure($title);
                if (is_array($value['filter'])) {
                    $this->select[$title] = $this->parseArray($value, $title);
                } else {
                    if ($value['filter'] === 'date') {
                        $this->select[$title] = easyCreateHTML::dataPickerBeetween($title);
                        continue;
                    }
                    if ($value['filter'] === 'basic') {
                        $this->select[$title] = '<option value="filled">заповнені</option><option value="empty">порожні</option>';
                        continue;
                    }
                    if ($value['filter'] === 'range') {
                        $this->select[$title] = self::range($title);
                        continue;
                    }

                    $structure = [
                        $title =>
                            [
                                'get'     => '',
                                'class'   => $class,
                                'setting' =>
                                    [
                                        'group' => $title,
                                        'where' => "$title IS NOT NULL",
                                    ],
                            ],
                    ];
                    if (isset($value['name']) || empty($value['filter'])) {
                        $structure[$title]['get'] = [$title];
                    } else {
                        $structure[$title]['get'] = [
                            $title,
                            $value['filter'],
                        ];
                    }
                    $this->select[$title] = $this->creatExitData($this->structure->set($structure)->get($title), $title,
                        $value);
                }
            }
        }
        return $this;
    }

    private function parseArray($value, $title)
    {
        $value = $value['filter'];
        if(isset($value['array'])){
            return $this->getData($value['array']);
        }
        if (isset($value['dictionary'])) {
            $key = $value['dictionary'];
            return $this->getData(DictionaryModel::getDictionary4Select($key));
        } else {
            if (!$this->structure->isEmpty($title)) {
                $this->structure->delete($title);
            }
            return $this->creatExitData($this->structure->set($value)->get($title), valid($value, 'text'),
                ['filter' => valid($value, 'value')]);
        }
    }

    private function getData(array $row)
    {
        $html = new easyCreateHTML();

        foreach ($row as $value => $text) {
            if(!$value || empty($value)){
                continue;
            }
            $html->option([
                'text'  => $text,
                'value' => $value,
            ])->end('option');
        }
        return preg_replace('/{%_value_%}/', $html->render(true), $this->template);
    }

    private function creatExitData($row, $title, $value)
    {
        $result = [];

        if (isset($value['name']) || (!empty($row[0]) && count($row[0]) == 1)) {
            foreach ($row as $val) {
                $result[$val[$title]] = isset($value['name']) ? getNameById($val[$title]) : $val[$title];
            }
        } else {
            foreach ($row as $val) {
                if (isset($val[$value['filter']]) && isset($val[$title])) {
                    $result[$val[$value['filter']]] = $val[$title];
                }
            }
        }
        return $this->getData(array_unique($result));
    }

    public static function range($parent): string
    {
        ob_start();
        ?>
        <form method="get">
            <input type="hidden" name="parent" value="<?= $parent ?>">
            <span class="dropdown">
                    <button class="range-filter dropdown-toggle" type="button" data-toggle="dropdown"></button>
                        <ul class="dropdown-menu">
                            <li class="range-filter-li">
                                <div id="slider"></div>
                            </li>
                            <li class="divider"></li>
                            <li><button class="range-send" type="submit">Фільтрувати</button></li>
                        </ul>
                </span>

        </form>


        <?php
        return ob_get_clean();
    }

    public function setTemplate(string $temp)
    {
        $this->template = $temp;
        return $this;
    }

    public function updateTitle(array $title)
    {
        unset($this->select);
        $this->title = $title;
        return $this;
    }
}