<?php

namespace Flames;

use Error;
use Flames\Collection\Arr;
use Flames\Orm\Database;
use Flames\Orm\Model\Data;

/**
 * Class Model
 *
 * The Model class is an abstract class that serves as the base for all model classes in the application.
 * It provides methods to handle saving, retrieving, and manipulating data in the database.
 *
 */
abstract class Model
{
    private static array $__setup  = [];
    private static array $_driver   = [];
    private static array $_data = [];
    private static array $_cast = [];
    private static array $_connection = [];

    private array|null $__changed = null;

    /**
     * Saves the current object to the database.
     *
     * @return void
     * @throws Error if no primary or unique column is found in the table.
     *
     */
    public function save() : void
    {
        $class = static::class;

        $indexColumn = null;
        foreach (self::$_data[$class]->column as $column) {
            if ($column->primary === true || $column->autoIncrement === true) {
                $indexColumn = $column;
                break;
            }
        }
        if ($indexColumn === null) {
            foreach (self::$_data[$class]->column as $column) {
                if ($column->unique === true) {
                    $indexColumn = $column;
                    break;
                }
            }
        }

        if ($indexColumn === null) {
            throw new Error('Missing primary or unique column in table ' . self::$table . ' using class ' . static::class . '.');
        }

        $data = $this->toArray();

        if ($data[$indexColumn->property] === null) {
            self::_verifyConnection($class);

            /** @var Database\QueryBuilder\DefaultEx $queryBuilder */
            $queryBuilder = self::$_driver[self::$_data[$class]->database]->getQueryBuilder($class);
            $queryBuilder->setModel(static::class);

            $insert = $queryBuilder->insert($data);
            foreach ($insert as $key => $value) {
                $this->{$key} = $value;
            }

            $this->__changed = null;
            return;
        }

        // Nothing to update
        if ($this->__changed === null || count($this->__changed) === 0) {
            return;
        }

        foreach ($data as $key => $_) {
            if (in_array($key, $this->__changed) === false) {
                unset($data[$key]);
            }
        }

        $data[$indexColumn->property] = $this->{$indexColumn->property};

        self::_verifyConnection($class);

        /** @var Database\QueryBuilder\DefaultEx $queryBuilder */
        $queryBuilder = self::$_driver[self::$_data[$class]->database]->getQueryBuilder($class);
        $queryBuilder->setModel(static::class);
        $queryBuilder->where($indexColumn->property, $this->{$indexColumn->property});
        $queryBuilder->update($data);
        $this->__changed = null;
    }

    /**
     * Retrieves the changed properties of the object.
     *
     * @param bool $onlyKeys Indicates whether to only return the keys or the complete data.
     * Default value is true.
     *
     * @return Arr|null If $onlyKeys is true, it returns an Arr object containing the keys of the changed properties.
     * If $onlyKeys is false, it returns an Arr object containing the changed data, where the keys are property names
     * and the values are the corresponding property values. If no properties have been changed, it returns null.
     */
    public function getChanged(bool $onlyKeys = true) : Arr|null
    {
        if ($this->__changed === null) {
            return null;
        }

        if ($onlyKeys === true) {
            return Arr($this->__changed);
        }

        $data = Arr();
        foreach ($this->__changed as $key) {
            $data[$key] = $this->{$key};
        }
        return $data;
    }

    /**
     * Converts the object to an Arr object.
     *
     * @return Arr Returns an Arr object containing the data of the object.
     */
    public function toArr() : Arr
    {
        return Arr($this->toArray());
    }

    /**
     * Converts the object to an array.
     *
     * @return array Returns an associative array where the keys are property names and the values are the corresponding
     * property values. If a property does not exist or is inaccessible, its value in the array will be null.
     */
    public function toArray() : array
    {
        $class = static::class;
        $data = [];

        foreach (self::$_data[$class]->column as $column) {
            try {
                $data[$column->property] = $this->{$column->property};
            } catch (\Error $_) {
                $data[$column->property] = null;
            }
        }

        return $data;
    }

    /**
     * Retrieves the name of the database table associated with the class.
     *
     * @return string The name of the database table associated with the class.
     */
    public static function getTable() : string|null
    {
        $class = static::class;
        if (isset(static::$table[$class]) === false) {
            return null;
        }

        return self::$table[$class];
    }

    /**
     * Retrieves the database name.
     *
     * This method returns the name of the database used by the class or object that calls it.
     *
     * @return string The name of the database.
     */
    public static function getDatabase() : string|null
    {
        $class = static::class;
        if (isset(self::$_data[$class]) === false) {
            return null;
        }

        return self::$_data[$class]->database;
    }

    // TODO: dynamic check migration on change table/database
//    public static function setTable(string $table) : bool
//    {
//        if (empty($table) === true)
//            return false;
//
//        self::$table = $table;
//        return true;
//    }
//
//    public static function setDatabase(string $database) : bool
//    {
//        if (empty($database) === true)
//            return false;
//
//        self::$database = $database;
//        return true;
//    }

    /**
     * @return void
     * @throws \Exception when the model can't get metadata.
     * @internal
     *
     * __constructStatic method is a static constructor that is called only once per class.
     *
     */
    public static function __constructStatic(): void
    {
        $class = static::class;
        if (isset(static::$__setup[$class]) === true && static::$__setup[$class] === true) {
            return;
        }

        self::__setup(Data::mountData(static::class));
        self::$__setup[$class] = true;
    }

    /**
     * @param Arr $data The data object containing database and table information.
     *
     * @return void
     * @throws \Exception when the model can't get metadata or model needs at least one column.
     * @internal
     *
     */
    private static function __setup(Arr $data): void
    {
        $class = static::class;
        self::$_data[$class] = $data;
        if (self::$_data[$class]->column->length === 0) {
            throw new \Exception('Model ' . static::class . 'need at least one column.');
        }
    }

    /**
     * @param Arr|array|null $data - Optional. An array or Arr object containing data to populate the object properties.
     * @param bool $ignoreChanged - Optional. Specifies whether to ignore tracking property changes. Default is false.
     * @throws \Exception - when the model can't get metadata.
     *
     * __construct method is a constructor that initializes a new instance of the class.
     * The constructor can take an optional $data parameter to populate object properties.
     * The $data parameter can be an array or an instance of the Arr class.
     * If $data is an Arr object, it is converted to an array before populating object properties.
     * If $data is an array, each key-value pair is assigned to the corresponding object property.
     * If $ignoreChanged is true, tracking of property changes is ignored by setting __changed property to null.
     */
    public function __construct(Arr|array|null $data = null, bool $ignoreChanged = false)
    {
        if ($data instanceof Arr) {
            $data = (array)$data;
        }

        if (is_array($data) === true) {
            foreach ($data as $key => $value) {
                try {
                    $this->__set($key, $value);
                } catch (\TypeError $_) {}
            }
        }

        $data = $this->toArray();
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }

        if ($ignoreChanged === true) {
            $this->__changed = null;
        }
    }

    /**
     * @param string $key The name of the property being set.
     * @param mixed $value The value to set the property to.
     * @return void
     *
     * __set method is used to set the value of a property dynamically.
     * It checks if the given key exists in the static $column array and sets the value accordingly.
     * If the key exists, it casts the value based on the type of the column and assigns it to the property.
     */
    public function __set(string $key, mixed $value)
    {
        $class = static::class;

        if (isset(self::$_data[$class]->column[$key]) === true) {
            try {
                $this->{$key} = self::cast($key, $value);
            } catch (\TypeError $e) {}

            if ($this->__changed === null) {
                $this->__changed = [];
            }

            if (in_array($key, $this->__changed) === false) {
                $this->__changed[] = $key;
            }
        }
    }

    public function set(string $key, mixed $value): void
    {
        $this->__set($key, $value);
    }

    /**
     * @param string $key The key of the property to access.
     * @return mixed|null The value of the property if it exists, or null otherwise.
     *
     * __get method is used to access properties dynamically.
     * It checks if the specified property exists and returns its value if it does,
     * otherwise it returns null.
     */
    public function __get(string $key)
    {
        if (isset($this->{$key}) === true) {
            return $this->{$key};
        }

        return null;
    }

    public function get(string $key)
    {
        return $this->__get($key);
    }

    /**
     * Casts the given key and value using the driver's cast method.
     *
     * @param string $key The key to cast.
     * @param mixed $value The value to cast.
     * @return mixed The casted value.
     */
    public static function cast(string $key, mixed $value = null) : mixed
    {
        $class = static::class;

        if (isset(self::$_data[$class]->column[$key]) === false) {
            throw new \Exception('Model key ' . $key . ' not found in class ' . $class);
        }

        self::_verifyCast($class);
        return self::$_cast[$class]::pos(self::$_data[$class]->column[$key], $value);
    }

    /**
     * Returns the database driver instance.
     *
     * @return Database\Driver|null The database driver instance, or null if it hasn't been set.
     * @throws \Exception When the model can't get metadata.
     *
     * getDriver method retrieves the database driver instance. If the driver hasn't been set yet,
     * it will be initialized by calling the static constructor.
     *
     */
    public static function getDriver(): mixed
    {
        $class = static::class;

        $database = self::$_data[$class]->database;
        if ($database === null) {
            $database = sha1($config);
        }

        if (isset(self::$_driver[$database]) === false || self::$_driver[$database] === null) {
            $_driver = new static();
            $_driver::_verifyConnection($class);
        }
        return self::$_driver[$database];
    }

    private static function _verifyConnection(string $class)
    {
        if (isset(self::$_connection[$class]) === false || self::$_connection[$class] === false) {
            self::$_connection[$class] = false;

            $database = self::$_data[$class]->database;
            if ($database === null) {
                $database = sha1($config);
            }

            $driver = Database\Driver::getByConfigAndDatabase(
                Database\DataFactory::getConfigByDatabase($database),
                self::$_data[$class]->database
            );

            $driver->migrate(self::$_data[$class]);
            self::$_driver[$database] = $driver;
        }
    }

    private static function _verifyCast(string $class)
    {
        if (isset(self::$_cast[$class]) === false || self::$_cast[$class] === false) {
            self::$_cast[$class] = Database\Cast\Factory::getByDatabaseType(
                Database\DataFactory::getConfigByDatabase(self::$_data[$class]->database)->type
            );
        }
    }

    public static function getMetadata()
    {
        $class = static::class;
        return self::$_data[$class];
    }
}