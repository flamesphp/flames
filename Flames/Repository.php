<?php

namespace Flames;

use /**
 * Handles exceptions thrown during the execution of the code.
 *
 * @package MyPackage
 */
    Exception;
use /**
 * Class Arr
 *
 * This class provides utility methods to work with arrays.
 */
    Flames\Collection\Arr;
use /**
 * Class Database
 *
 * This class provides basic database operations using PDO in PHP
 */
    Flames\ORM\Database;
use /**
 * Class Data
 *
 * This class represents the data repository for the Flames ORM framework.
 * It provides methods to interact with the underlying data storage.
 */
    Flames\ORM\Repository\Data;

/**
 * Class Repository
 *
 * The Repository class is an abstract class that provides a base implementation for repositories,
 * which are responsible for retrieving and manipulating data from a data source.
 */
abstract class Repository
{
    private static bool $__setup = false;
    private static Database\Driver|null $driver = null;

    private static string $database;
    private static string $model;

    /**
     * Class constructor for static methods.
     *
     * @throws Exception
     *
     * @return void
     */
    public static function __constructStatic(): void
    {
        if (self::$__setup === true) {
            return;
        }

        self::__setup(Data::mountData(static::class));
        self::$__setup = true;
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
        if ($data->model === null || class_exists($data->model) === false) {
            throw new Exception('Repository ' . static::class . ' need a model.');
        }

        if ($data->database === null) {
            $data->database = $data->model::getDatabase();
            if ($data->database === null) {
                throw new Exception('Repository ' . static::class . ' need a database, not founded in model.');
            }
        }

        self::$database = $data->database;
        self::$model    = $data->model;
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
        if (self::$driver === null) {
            self::$driver = self::$model::{'getDriver'}();
        }
        return self::$driver;
    }
}