<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\ORM\Database;
use Flames\ORM\Repository\Data;

abstract class Repository
{
    private static bool $__setup = false;
    private static Database\Driver|null $driver = null;

    private static string $database;
    private static string $model;

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
        if ($data->model === null || class_exists($data->model) === false) {
            throw new \Exception('Repository ' . static::class . ' need a model.');
        }

        if ($data->database === null) {
            $data->database = $data->model::getDatabase();
            if ($data->database === null) {
                throw new \Exception('Repository ' . static::class . ' need a database, not founded in model.');
            }
        }

        self::$database = $data->database;
        self::$model    = $data->model;
    }

    public static function get(mixed $index) : Model|null
    {
        $driver = self::getDriver();
        $data   = $driver->getByIndex($index);
        dump($data);
        exit;

        return null;
    }

    public static function getDriver(): Database\Driver|null
    {
        if (self::$driver === null) {
            self::$driver = self::$model::getDriver();
        }
        return self::$driver;
    }
}