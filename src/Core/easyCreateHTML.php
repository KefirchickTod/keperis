<?php


namespace src\Core;


use Closure;

class easyCreateHTML
{

    public static $toolBar = '';
    /** @var array */
    protected static $noCloseTags = [
        'area',
        'base',
        'br',
        'col',
        'frame',
        'hr',
        'img',
        'input',
        'link',
        'meta',
        'param',
        'text',
    ];
    /* @var string */
    private $html = '';
    /* @var array */
    private $stack = [];


    public static function dataPickerBeetween($parent = '')
    {

        return self::create()
            ->div([
                'id'    => 'pageWrapper',
                'style' => '    height: 86%;
                                display: flex;
                                align-items: flex-end;',
            ])
            ->div(
                [
                    'id'    => 'pageMasthead',
                    'class' => 'pageSection',
                ])->end('div')
            ->div(
                [
                    'id'    => 'pageContentArea',
                    'class' => 'pageSection',
                    'style' => 'margin-top:0!important;',
                ])->form()
            ->input(
                [
                    'type' => 'text',

                    'name'        => 'txtDateRange',
                    'class'       => 'inputField shortInputField dateRangeField',
                   // 'placeholder' => 'Date',
                    'style'       => 'width: 100%;background-color: white;
                                            border: 1px solid #aaa;
                                            border-radius: 4px;
                                            height: 33px;
                                           
                                            clear: both;',
                    'value'       => '',
                ])->input([
                'type'  => 'hidden',
                'name'  => 'parent',
                'class' => 'dateParent',
                'value' => $parent,
            ])
            ->end('form')
            ->end('div')
            ->end('div')
            ->__toString();


    }

    /**
     * @return easyCreateHTML
     */
    public static function create()
    {
        return new static();
    }

    /**о
     * @param array
     * @param string
     * @return array|string
     */
    public static function popOption(&$options, $name)
    {
        if (array_key_exists($name, $options)) {
            $option = $options[$name];
            unset($options[$name]);
            return $option;
        }
        return '';
    }

    public static function panel_toolbox($active = true, $candelete = true): string
    {
        if (self::$toolBar !== '' && self::$toolBar && $active == true) {
            return self::$toolBar;
        }
        $result = html()->ul(['class' => 'nav navbar-right panel_toolbox'])
            ->li()->a(['class' => 'collapse-link'])->i(['class' => $active == false ? 'fa fa-chevron-down' : 'fa fa-chevron-up'])->end('i')->end('a')->end('li')
            ->li(['style' => "display : " . ($candelete == 'true' ? 'block' : 'none')])->a(['class' => 'close-link'])->i(['class' => 'fa fa-close'])->end('i')->end('a')->end('li')
            ->end('ul')->render(true);
        self::$toolBar = $result;
        return $result;
    }

    /**
     * Simple foreach create with tags
     * @param array
     * @param string
     * @return easyCreateHTML
     * */
    public function each($setting, $tags = null)
    {
        $attType = true;
        if (is_array($setting)) {
            if (is_int(key($setting))) {
                if (!is_array($setting[0])) {
                    $attType = false;
                }
            }
        }
        $tags = $tags ?: array_pop($this->stack);
        foreach ($setting as $key => $value) {
            if ($attType == false) {
                $this->$tags(['text' => "$value"])->end();
            } else {
                $this->$tags($setting[$key])->end($tags);
            }
        }
        return $this;
    }

    public function breaking()
    {
        return $this;
    }

    public function insert($html)
    {
        if (is_object($html) && $html instanceof Closure) {
            $html = call_user_func($html);
        }
        $this->html .= $html;
        return $this;
    }

    /**
     * @return easyCreateHTML
     */
    public function __call($method, $ps)
    {
        array_unshift($ps, $method);
        return call_user_func_array([$this, 'tag'], $ps);
    }

    public function __toString()
    {
        return $this->render(true);
    }

    /**
     * Render current tags
     * @param boolean $return return or print
     * @return easyCreateHTML|string|void
     *
     */
    public function render($return = false)
    {
        while ($this->stack) {
            $this->end();
        }
        //var_dump($this->html);
        $html = $this->html;
        $this->html = '';
        if ($return) {
            return $html;
        }
        echo $html;
    }

    /**
     * Close tag with name, or last tag
     * @param string $name tag name
     * @return easyCreateHTML
     */
    public function end($name = '')
    {
        if ($this->stack) {
            $name2 = array_pop($this->stack);
        }
        if (!$name) {
            $name = $name2;
        }
        $this->html .= '</' . $name . '>';
        return $this;
    }

    public function __clone()
    {
        $this->html = '';
        $this->stack = [];
    }

    public function clean()
    {
        return clone $this;
    }

    /**
     * @param string $name tag name
     * @param array|string $options tag attributes
     * @return easyCreateHTML
     */
    protected function tag($name, $options = null, $autoClose = true)
    {

        $name = strtolower($name);
        if ($name === 'text') {
            $this->stack[] = $name;
            return $this;
        }
        $close = in_array($name, self::$noCloseTags);
        if ($name === 'html' && is_string($options)) {
            $this->html .= $options;
            return $this;
        }
        $options = $options ?: [];
        $attrs = [];
        $options = (isset($options['text'])) ? $this->pushBack('text', $options) : $options;
        $text = '';
        if (is_array($options)) {
            foreach ($options as $attr => $value) {
                if ($attr === 'text') {
                    $text = $value;
                } else {
                    $attrs[] = $attr . '="' . $this->safe(is_array($value) ? implode(' ', $value) : $value) . '"';
                }
            }
        } elseif (is_string($options)) {
            $attrs = [$options];
        }

        $this->html .= '<' . $name . ($attrs ? ' ' . implode(' ',
                $attrs) : '') . ($close ? '/' : '') . '>' . $text ?: $text;
        if (!$close && $autoClose) {
            $this->stack[] = $name;
        }
        return $this;
    }

    /**
     * @param $key
     * @param $arr
     * @return mixed
     */
    public static function pushBack($key, $arr)
    {
        $r = $arr[$key];
        unset($arr[$key]);
        $arr[$key] = $r;
        return $arr;
    }

    /**
     * Return safe html string
     * @param string|array $value
     * @return string
     */
    public static function safe($value)
    {
        if (is_array($value)) {
            foreach ($value as &$item) {
                $item = self::safe($item);
            }
            return $value;
        } else {
            if (is_scalar($value)) {
                return htmlspecialchars('' . $value, ENT_QUOTES);
            }
        }
        return "TYPE ERROR " . gettype($value);
    }

}