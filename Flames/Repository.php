<?php

namespace Flames;

use Exception;
use Flames\Collection\Arr;
use Flames\Orm\Database;
use Flames\Orm\Repository\Data;

/**
 * Class Repository
 *
 * The Repository class is an abstract class that provides a base implementation for repositories,
 * which are responsible for retrieving and manipulating data from a data source.
 */
abstract class Repository
{
    private static array $__setup = [];
    private static array $driver  = [];

    private static array $database = [];
    private static array $model    = [];

    /**
     * Class constructor for static methods.
     *
     * @throws Exception
     *
     * @return void
     */
    public static function __constructStatic(): void
    {
        $class = static::class;

        if (isset(static::$__setup[$class]) === true && self::$__setup[$class] === true) {
            return;
        }

        self::__setup(Data::mountData(static::class));
        self::$__setup[$class] = true;
    }

    /**
     * Setup method for initializing data and variables.
     *
     * @param Arr $data The data to set up.
     *
     * @return void
     * @throws Exception when the repository does not have a model or database.
     *
     */
    private static function __setup(Arr $data): void
    {
        $class = static::class;

        if ($data->model === null || class_exists($data->model) === false) {
            throw new Exception('Repository ' . static::class . ' need a model.');
        }

        if ($data->database === null) {
            $data->database = $data->model::getDatabase();
            if ($data->database === null) {
                throw new Exception('Repository ' . static::class . ' need a database, not founded in model.');
            }
        }

        self::$database[$class] = $data->database;
        self::$model[$class]    = $data->model;
    }

    /**
     * Retrieves a Model instance based on the provided index.
     *
     * @param mixed $index The index used to retrieve the Model.
     * @return Model|null The retrieved Model instance, or null if not found.
     */
    public static function get(mixed $index) : Model|null
    {
        return self::getDriver()->getByIndex($index);
    }

    /**
     * Retrieves data from the driver using specified filters.
     *
     * @param Arr|array $filters The filters to be applied.
     * @param Arr|array|null $options (Optional) Additional options for getting the data.
     *
     * @return Arr|null The retrieved data as an Arr object, or null if no data is found.
     */
    public static function withFilters(Arr|array $filters, Arr|array $options = null) : Arr|null
    {
        return self::getDriver()->getWithFilters($filters, $options);
    }

    /**
     * Returns the database driver instance.
     *
     * @return Database\Driver|null The database driver instance or null if not set.
     */
    public static function getDriver(): Database\Driver|null
    {
        $class = static::class;

        if (isset(self::$driver[$class]) === false || self::$driver[$class] === null) {
            self::$driver[$class] = self::$model[$class]::{'getDriver'}();
        }
        return self::$driver[$class];
    }
}