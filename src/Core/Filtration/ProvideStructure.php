<?php


namespace src\Core\Filtration;



use src\Interfaces\Structure\provideStructureInterface;
use src\Structure\Structure;

class ProvideStructure extends Structure implements provideStructureInterface
{

    function __construct(array $setting = null)
    {
        parent::__construct($setting);
    }

    /**
     * @param $setting
     */
    public static function multiJoin($setting, &$query)
    {
        $structure = new ProvideStructure();
        $result =
            [
                'join'   => [],
                'select' => [],
                'order'  => [],
                'where'  => [],
            ];
        foreach ($setting as $name => $value) {
            $tables =
                $structure->setStructure($setting)
                    ->getOnlyQuery($name);

            $result['join'][] = $setting[$name]['type_join'] . ' JOIN ' . $tables['table'] . ' ON ' . $setting[$name]['on'] . ' ' . ((!empty($tables['join'])) ? $tables['join'] : ' ');
            if (isset($tables['order'])) {
                $result['order'][] = $tables['order'];
            }
            if (isset($tables['where'])) {
                $result['where'][] = $tables['where'];
            }
            $result['select'][] = $tables['select'];
        }
        addToArray($query, 'select', ' , ' . implode(' , ', $result['select']));
        addToArray($query, 'join', implode(' ', $result['join']));
        if (!empty($result['where']) && is_string($result['where']) && strlen(trim($result['where'])) > 1) {
            addToArray($query, 'where', implode(' ', $result['where']));
        }
        $order = !empty(trim(implode(' ', $result['order']))) ? implode(' ', $result['order']) : '';
        addToArray($query, 'order', $order);
    }

    /**
     * @param null $colum
     * @return array
     */
    public function getOnlyQuery($colum = null): array
    {
        return $this->outPutStructure($colum ?: $this->mainName, false, true);
    }

    /**
     * @param null $colum
     * @return array|mixed|null
     */
    public function getArraySetting($colum = null)
    {
        return $this->outPutStructure($colum ?: $this->mainName, true, false);
    }

    public function outPutStructure($column = 'user', $arraySetting = false, $onlyQuery = false)
    {

        if ($arraySetting == true || $onlyQuery != false) {
            return parent::get($column ?: $this->mainName, true);
        }
        return parent::get($column ?: $this->mainName);
    }

    /**
     * @return ProvideStructure
     * Передає управліня функції яка створює масив з обєктами і даними які потрібно отримати
     * */
    public function setStructure(array $setting)
    {
        parent::set($setting);
        return $this;
    }

    /**
     * @return ProvideStructure
     */
    public static function creatSelf()
    {
        return new static();
    }

    /** Повертає обєктои
     * @return array
     */
    public function getClassFromMethod()
    {
        return $this->classes;
    }

    /**
     * @param $limit string
     * @return  $this
     */
    public function setLimit($limit)
    {
        $this->setting['limit'] = $limit;
        return $this;
    }

    public function getTableName()
    {
        return $this->classes()->getTableName();
    }


}