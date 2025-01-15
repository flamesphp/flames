<?php

namespace Flames;

use Exception;
use Flames\Collection\Arr;
use Flames\Orm\Database;
use Flames\Orm\Database\QueryBuilder;
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
    private static array $_driver  = [];

    private static array $_data = [];

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

        self::$_data[$class] = $data;
    }

    protected static function getQueryBuilder(): \Flames\Orm\Database\QueryBuilder\DefaultEx
    {
        $class = static::class;

        $model = self::$_data[$class]->model;
        $model::getMetadata(true); // Force verify connection

        /** @var Database\QueryBuilder\DefaultEx $queryBuilder */
        $queryBuilder = self::getDriver()->getQueryBuilder(self::$_data[$class]->model);
        $queryBuilder->setModel(self::$_data[$class]->model);
        return $queryBuilder;
    }

    /**
     * Retrieves a Model instance based on the provided index.
     *
     * @param mixed $index The index used to retrieve the Model.
     * @return Model|null The retrieved Model instance, or null if not found.
     */
    public static function get(mixed $index) : Model|null
    {
        $class = static::class;
        $indexColumn = self::_getIndexColumn();

        $queryBuilder = self::getQueryBuilder();
        $queryBuilder->where($indexColumn->property, $index);
        $queryBuilder->limit(1);
        $rows = $queryBuilder->get();

        if ($rows->count === 0) {
            return null;
        }

        return $rows[0];
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
        $filters = self::_parseFilters($filters);
        $queryBuilder = self::getQueryBuilder();

        foreach ($filters as $filter) {
            if ($filter[3] === 'AND') {
                if ($filter[1] === 'LIKE') {
                    $queryBuilder = $queryBuilder->whereLike($filter[0], $filter[2]);
                } else {
                    $queryBuilder = $queryBuilder->where($filter[0], $filter[1], $filter[2]);
                }
            } else {
                if ($filter[1] === 'LIKE') {
                    $queryBuilder = $queryBuilder->orWhereLike($filter[0], $filter[2]);
                } else {
                    $queryBuilder = $queryBuilder->orWhere($filter[0], $filter[1], $filter[2]);
                }
            }
        }

        // TODO: $options -> order, limit
        return $queryBuilder->get();
    }

    protected static function _parseFilters(Arr|array $filters) : array
    {
        if ($filters instanceof Arr) {
            $filters = (array)$filters;
        }

        $_filters = [];
        foreach ($filters as $key => $filter) {
            if (is_array($filter) || $filter instanceof Arr) {
                $filterCount = count($filter);
                if ($filterCount === 2) {
                    $_filters[] = [$filter[0], '=', $filter[1], 'AND'];
                }
                elseif ($filterCount === 3) {
                    $_filters[] = [$filter[0], strtoupper($filter[1]), $filter[2], 'AND'];
                }
                elseif ($filterCount === 4) {
                    $_filters[] = [$filter[0], strtoupper($filter[1]), $filter[2], $filter[3]];
                }
                else {
                    throw new Exception('Invalid filter data.');
                }
                continue;
            }

            $_filters[] = [$key, '=', $filter, 'AND'];
        }

        return $_filters;
    }


    /**
     * Returns the database driver instance.
     *
     * @return Database\Driver|null The database driver instance or null if not set.
     */
    public static function getDriver(): mixed
    {
        $class = static::class;

        if (isset(self::$_driver[$class]) === false || self::$_driver[$class] === null) {
            self::$_driver[$class] = self::$_data[$class]->model::{'getDriver'}();
        }

        return self::$_driver[$class];
    }

    protected static function _getIndexColumn()
    {
        $class = static::class;

        $metadata = self::$_data[$class]->model::getMetadata();

        $indexColumn = null;
        foreach ($metadata->column as $column) {
            if ($column->primary === true || $column->autoIncrement === true) {
                $indexColumn = $column;
                break;
            }
        }
        if ($indexColumn === null) {
            foreach ($metadata->column as $column) {
                if ($column->unique === true) {
                    $indexColumn = $column;
                    break;
                }
            }
        }

        if ($indexColumn === null) {
            throw new Error('Missing primary or unique column in table ' . self::$table . ' using class ' . static::class . '.');
        }


        return $indexColumn;
    }
}