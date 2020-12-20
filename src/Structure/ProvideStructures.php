<?php


namespace src\Structure;


use src\Collection;

abstract class ProvideStructures extends Collection
{
    const namespace = 'App\src\Structure\ProvideStructures\\';
    public static $exception = ['table', 'id', 'prefix', 'size'];
    /**
     * @var string
     */
    protected $name;
    protected $sqlSetting = [];
    /**
     * @var string
     */
    protected $factoryName;
    protected $pattern = [];

    function __construct(array $item = [])
    {
        $item = $this->sqlSetting;
        parent::__construct($item);
    }

    public static function getAll($structure = true)
    {
        $static = new static();
        $get = array_keys($static->getPattern());
        foreach ($get as $key => $value) {
            if (in_array($value, self::$exception)) {
                unset($get[$key]);
            }
        }
        structure()->delete('getAll');
        return $structure === true ? [
            'getAll' =>
                [
                    'get'   => $get,
                    'class' => 'auto',
                ],
        ] : $get;


    }

    public function getPattern($key = false)
    {

        if ($key == false) {
            return $this->all();
        }
        return $this->get($key);
    }

    public function getTableName(): string
    {
        if (!isset($this->name)) {
            $this->name = $this->getPattern('table');
        }

        return $this->name ;
    }

    public function getFactoryName()
    {
        return $this->factoryName;
    }

    public function getTemplate($key = null)
    {
        return $this->pattern[$key] ?? $this->pattern;
    }


    public function historyPatternGet(): array
    {
        return [
            $this->getPattern('historyData'),
            $this->getPattern('source'),
            $this->getPattern('historyDescription'),
            $this->getPattern('historyAuthor'),
        ];
    }

}