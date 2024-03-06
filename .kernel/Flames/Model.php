<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\ORM\Database;
use Flames\ORM\Model\Data;
use Flames\ORM\Table;
use JetBrains\PhpStorm\NoReturn;
use PDO;

abstract class Model
{
    private static bool $__setup = false;
    private static Database\Driver|null $driver;
    private static string $database;
    private static string $table;
    private static Arr $column;

    public function save()
    {
        $primaryColumn = null;
        foreach (self::$column as $column) {
            if ($column->primary === true || $column->autoIncrement === true) {
                $primaryColumn = $column;
                break;
            }
        }

        if ($primaryColumn === null) {
            throw new \Exception('Missing primary column in table ' . self::$table . ' using class ' . static::class . '.');
        }

        $data = $this->toArray();

        if ($data[$primaryColumn->property] === null) {
            unset($data[$primaryColumn->property]);
            return self::$driver->insert($data);
        }

        $id = $data[$primaryColumn->property];
        unset($data[$primaryColumn->property]);
        return self::$driver->update($id, $data);
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

    // TODO: dynamic check migration on change table/database
//    public static function getTable() : string
//    {
//        return self::$table;
//    }
//
//    public static function setTable(string $table) : bool
//    {
//        if (empty($table) === true)
//            return false;
//
//        self::$table = $table;
//        return true;
//    }
//
//    public static function getDatabase() : string
//    {
//        return self::$database;
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
            throw new \Exception('Model need at least one column.');
        }

        self::$column = $data->column;
        self::$driver = (new Database\RawConnection(self::$database))->getDriver($data);
    }

    #[NoReturn]
    public function __construct(Arr|array|null $data = null)
    {
        if ($data instanceof Arr) {
            $data = (array)$data;
        }

        if (is_array($data) === true) {
            foreach ($data as $key => $value) {
                $this->__set($key, $value);
            }
        }
    }

    public function __set(string $key, mixed $value)
    {
        if (isset(self::$column[$key]) === true) {
            $this->{$key} = self::__parse(self::$column[$key], $value);
        }
    }

    public function __get(string $key)
    {
        if (isset($this->{$key}) === true) {
            return $this->{$key};
        }

        return null;
    }

    private static function __parse(Arr $column, mixed $value = null) : mixed
    {
        // TODO: parse data
        return $value;
    }
}