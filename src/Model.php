<?php


namespace src;


use App\Provides\Mask;
use src\Core\DB;
use src\Interfaces\ModelInterface;
use src\Structure\Structure;
use src\Traits\HasAttributes;
use Error;

class Model extends Collection implements ModelInterface
{

    use HasAttributes;
    /**
     * @var bool
     */
    public static $unguarded = false;
    public $validation = [];
    /**
     * @var array|null
     */
    public $fillable = [];
    /**
     * @var Structure
     */
    public $structure;
    /**
     * @var string
     */
    public $bc_table;
    /**
     * @var null
     */
    public $mask = null;
    /**
     * @var DB
     */
    public $connection;
    /**
     * @var array
     */
    public $guarded = ['*'];


    protected $xlsxImportUnique = [];
    /**
     * @var string|null
     */
    protected $bc_table_id;

    public function __construct(array $arguments = [], $data = [])
    {
        parent::__construct($data);

        $this->fill($arguments);
        $this->connection = db();
        $this->structure = structure();
    }

    /**
     * @param $argument
     * @return $this
     */
    public function fill($argument)
    {

        foreach ($this->fillableFromArray($argument) as $key => $value) {
            $key = $this->removeTableFromKey($key);

            // The developers may choose to place some attributes in the "fillable" array/
            // which means only those attributes may be set through mass assignment to
            // the model, and all others will just get ignored for security reasons.
            if ($this->isFillable($key)) {


                $this->setAttribute($key, $value);


            }
        }

        return $this;
    }

    public function delete($fields){
        if(!is_array($fields)){
            $fields = [$this->id() => $fields];
        }
        return $this->connection->deleteSql($this->bc_table, $fields);
    }
    /**
     * @param array $attributes
     * @return array
     */
    public function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0 && !static::$unguarded) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }

        return $attributes;
    }

    /**
     * @return array|null
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function removeTableFromKey($key)
    {
        return str_contains($key, '.') ? last(explode('.', $key)) : $key;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isFillable($key)
    {
        if (static::$unguarded) {
            return true;
        }

        // If the key is in the "fillable" array, we can of course assume that it's
        // a fillable attribute. Otherwise, we will check the guarded array when
        // we need to determine if the attribute is black-listed on the model.
        if (in_array($key, $this->getFillable())) {
            return true;
        }

        // If the attribute is explicitly listed in the "guarded" array then we can
        // return false immediately. This means this attribute is definitely not
        // fillable and there is no point in going any further in this method.
        if ($this->isGuarded($key)) {
            return false;
        }

        return empty($this->getFillable()) &&
            !startsWith($key, '_');
    }

    /**
     * @param $key
     * @return bool
     */
    public function isGuarded($key)
    {
        return in_array($key, $this->getGuarded()) || $this->getGuarded() == ['*'];
    }

    /**
     * @return array
     */
    public function getGuarded()
    {
        return $this->guarded;
    }

    public function getImportUnique($key = null)
    {

        if ($key) {
            return $this->xlsxImportUnique[$key] ?? null;
        }
        return array_keys($this->xlsxImportUnique);
    }

    /**
     * @return bool
     */
    public function totallyGuarded()
    {
        return count($this->getFillable()) === 0 && $this->getGuarded() == ['*'];
    }

    public function save($valid = true, $self = false)
    {
        $toSave = $valid ? $this->connection->valid($this->bc_table, $this->attributes) : $this->attributes;

        $save = $this->connection->insertOrUpdateSql($this->bc_table, $toSave);
        if ($self === true) {
            return new static();
        }
        return intval($save);
    }

    public function insert($id)
    {
        $valid = $this->connection->valid($this->bc_table, $this->getAttributes());
        return $this->connection->insertOrUpdateSql($this->bc_table, array_merge($valid, [$this->id() => $id]));
    }



    public function id()
    {
        if (!$this->bc_table_id) {
            $this->bc_table_id = $this->bc_table . "_id";
        }
        return $this->bc_table_id;

    }

    public function update($id)
    {
        return $this->connection->updateSql($this->bc_table,
            $this->connection->valid($this->bc_table, $this->getAttributes()), "{$this->bc_table}_id = $id");
    }

    public function forwardCallTo($object, $method, $parameters)
    {
        try {
            return $object->{$method}(...$parameters);
        } catch (Error $e) {
            $pattern = '~^Call to undefined method (?P<class>[^:]+)::(?P<method>[^\(]+)\(\)$~';

            if (!preg_match($pattern, $e->getMessage(), $matches)) {
                throw $e;
            }

            if ($matches['class'] != get_class($object) ||
                $matches['method'] != $method) {
                throw $e;
            }

        }
        return false;
    }


    public function __call($method, $parameters)
    {
        return $this->$method($parameters);

    }


    public function getStructure(): Structure
    {
        return $this->structure;
    }

    /**
     * @param Structure $structure
     * @return static
     */
    public function withStructure(Structure $structure)
    {
        $clone = clone $this;
        $clone->structure = $structure;
        return $clone;
    }

    /**
     * @param Mask $mask
     * @return static
     */
    public function withMask(Mask $mask)
    {
        $clone = clone $this;
        $clone->mask = $mask;
        return $clone;
    }

    public function getMask(): Mask
    {
        try {
            if (is_object($this->mask) && $this->mask instanceof Mask) {
                return $this->mask;
            } elseif (!$this->mask) {
                throw new \RuntimeException("Get mask must return instance of class Mask no null");
            }
        } catch (\RuntimeException $exception) {
            error_log($exception->getMessage());
        }
        $this->mask = new $this->mask;
        // debug($this->mask);
        return $this->getMask();
    }

    /**
     * @param $id
     * @param null $dataArray
     * @param null $key
     * @return $this
     */
    public function find($id, $dataArray = null, $key = null)
    {
        if ($dataArray) {
            set_structure_setting($dataArray, 'id = ' . $id, 'where', $key);
            $row = $this->structure->set($dataArray)->get($key ?: key($dataArray));
        } else {

            $row = $this->connection->selectSql($this->bc_table, '*', "{$this->bc_table}_id = $id");
        }
        return $this->withData(valid($row, 0, []));
    }

    public function withData($data)
    {
        return new static($this->fillable, $data);
    }

    public function findIdByXlsxValue($column, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        $value = join(', ', $value);

        $column = $this->xlsxImportUnique[$column];

        if (!$this->bc_table_id) {
            $this->bc_table_id = $this->bc_table . "_id";
        }

        $row = $this->connection->selectSql($this->bc_table, $this->bc_table_id . " AS id", "$column IN ($value)");
        return $row;
    }

    public function where(string $query, $need = '*')
    {
        $fetch = $this->connection->selectSql($this->bc_table, $need, $query);
        if (!empty($fetch)) {
            return $this->withData($fetch);
        }
        return $this->withData([]);
    }


    public function setDefault(string $column, $value)
    {
        return $this->connection->getConnection()->query("ALERT TABLE " . $this->bc_table . " ALERT $column SET DEFAULT " . $value);
    }


    public function validation($arguments, array $validation = null)
    {
        $this->validation = $validation ?: $this->validation;
        $keys = array_unique(array_merge(array_keys($this->validation), array_keys($arguments)));

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->validation) && $this->validation[$key] instanceof \Closure) {
                $arguments[$key] = call_user_func($this->validation[$key], $arguments[$key] ?? '');
            }
        }
        return $arguments;
    }

}