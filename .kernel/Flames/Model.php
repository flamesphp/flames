<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\ORM\Database;
use Flames\ORM\Model\Data;

abstract class Model
{
    private static bool $__setup = false;
    private static Database\Driver|null $driver = null;
    private static string $database;
    private static string $table;
    private static Arr $column;

    private array|null $__changed = null;

    public function save() : void
    {
        $indexColumn = null;
        foreach (self::$column as $column) {
            if ($column->primary === true || $column->autoIncrement === true) {
                $indexColumn = $column;
                break;
            }
        }
        if ($indexColumn === null) {
            foreach (self::$column as $column) {
                if ($column->unique === true) {
                    $indexColumn = $column;
                    break;
                }
            }
        }

        if ($indexColumn === null) {
            throw new \Exception('Missing primary or unique column in table ' . self::$table . ' using class ' . static::class . '.');
        }

        $data = $this->toArray();

        if ($data[$indexColumn->property] === null) {
            $insert = self::$driver->insert($data);
            if ($insert !== true) {
                foreach ($insert as $key => $value) {
                    $this->{$key} = $value;
                }
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

        self::$driver->update($indexColumn->property, $data);
        $this->__changed = null;
    }

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

    public function toArr() : Arr
    {
        return Arr($this->toArray());
    }

    public function toArray() : array
    {
        $data = [];

        foreach (self::$column as $column) {
            try {
                $data[$column->property] = $this->{$column->property};
            } catch (\Error $_) {
                $data[$column->property] = null;
            }
        }

        return $data;
    }

    public static function getTable() : string
    {
        return self::$table;
    }

    public static function getDatabase() : string
    {
        return self::$database;
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

    public static function __constructStatic(): void
    {
        if (self::$__setup === true) {
            return;
        }

        self::__setup(Data::mountData(static::class));
        self::$__setup = true;
    }

    private static function __setup(Arr $data): void
    {
        self::$database = $data->database;
        self::$table    = $data->table;

        if ($data->column->length === 0) {
            throw new \Exception('Model ' . static::class . 'need at least one column.');
        }

        self::$column = $data->column;
        self::$driver = (new Database\RawConnection(self::$database))->getDriver($data);
    }

    public function __construct(Arr|array|null $data = null, bool $ignoreChanged = false)
    {
        if ($data instanceof Arr) {
            $data = (array)$data;
        }

        if (is_array($data) === true) {
            foreach ($data as $key => $value) {
                $this->__set($key, $value);
            }
        }

        if ($ignoreChanged === true) {
            $this->__changed = null;
        }
    }

    public function __set(string $key, mixed $value)
    {
        if (isset(self::$column[$key]) === true) {
            $this->{$key} = self::cast($key, $value);

            if ($this->__changed === null) {
                $this->__changed = [];
            }

            if (in_array($key, $this->__changed) === false) {
                $this->__changed[] = $key;
            }
        }
    }

    public function __get(string $key)
    {
        if (isset($this->{$key}) === true) {
            return $this->{$key};
        }

        return null;
    }

    public static function cast(string $key, mixed $value = null) : mixed
    {
        return self::$driver->cast($key, $value);
    }

    public static function getDriver(): Database\Driver|null
    {
        if (self::$driver === null) {
            new static();
        }
        return self::$driver;
    }
}