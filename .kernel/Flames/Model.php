<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\ORM\Database;
use Flames\ORM\Table;
use JetBrains\PhpStorm\NoReturn;

abstract class Model
{
    private const __VERSION__ = 5;

    private static bool $__setup = false;
    private static string $database;
    private static string $table;
    private static Arr $column;

    public static function getTable() : string
    {
        return self::$table;
    }

    public static function setTable(string $table) : bool
    {
        if (empty($table) === true)
            return false;

        self::$table = $table;
        return true;
    }

    public static function getDatabase() : string
    {
        return self::$database;
    }

    public static function setDatabase(string $database) : bool
    {
        if (empty($database) === true)
            return false;

        self::$database = $database;
        return true;
    }

    public static function __constructStatic()
    {
        if (self::$__setup === true) {
            return;
        }

        $class = static::class;
        $path = (ROOT_PATH . str_replace('\\', '/', $class) . '.php');

        $basePath  = (ROOT_PATH . '.cache/model/');
        $cachePath = ($basePath . sha1($class) . '.blob');
        $currentTime = filemtime($path);

        if (file_exists($cachePath) === true && filemtime($cachePath) === $currentTime) {
            $data = unserialize(file_get_contents($cachePath));
            if ($data->version === self::__VERSION__) {
                self::__setup($data);
                return;
            }
        }

        $data = self::__getReflection($class);
        $success = @file_put_contents($cachePath, serialize($data));
        if ($success === false) {
            if (is_dir($basePath) === false) {
                mkdir($basePath, 0777, true);
                @file_put_contents($cachePath, serialize($data));
            }
        }
        @touch($cachePath, $currentTime);

        self::__setup($data);
        self::$__setup = true;
    }

    private static function __getReflection(string $class) : Arr
    {
        $data = Arr([
            'version'  => self::__VERSION__,
            'database' => null,
            'table'    => null,
            'column'   => Arr()
        ]);

        $reflection = new \ReflectionClass($class);

        // Get database/table
        $attributes = $reflection->getAttributes();
        foreach($attributes as $attribute) {
            $attributeName = $attribute->getName();

            if ($attributeName === \Flames\ORM\Database::class) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['name']))
                    $data->database = $arguments['name'];
            }

            elseif ($attributeName === \Flames\ORM\Table::class) {
                $arguments = $attribute->getArguments();
                if (isset($arguments['name']))
                    $data->table = $arguments['name'];
            }
        }
        if ($data->table === null) {
            $data->table = str_replace('\\', '_', strtolower(substr($class, 17)));
        }

        // Get columns
        $properties = $reflection->getProperties();
        foreach($properties as $property) {
            $columnProperty = $property->getName();
            if (str_starts_with($columnProperty, '_') === true) {
                continue;
            }

            $column = Arr([
                'property'      => $columnProperty,
                'name'          => null,
                'type'          => null,
                'size'          => null,
                'nullable'      => false,
                'default'       => $property->getDefaultValue(),
                'primary'       => false,
                'index'         => false,
                'unique'        => false,
                'autoIncrement' => false,
            ]);

            $type = $property->getType();

            if ($type instanceof \ReflectionUnionType) {
                $types = $type->getTypes();

                $foundType = false;
                foreach ($types as $type) {
                    $typeName = $type->getName();

                    if ($typeName === 'null') {
                        $column->nullable = true;
                    } elseif ($foundType === false) {
                        $column->type = $typeName;
                        $foundType = true;
                    }
                }
            } else {
                $column->nullable = $type->allowsNull();
                $column->type     = $type->getName();
            }

            $attributes = $property->getAttributes();
            foreach($attributes as $attribute) {
                if ($attribute->getName() === ORM\Column::class) {

                    $arguments = $attribute->getArguments();

                    if (isset($arguments['nullable']) === true) {
                        $column->nullable = $arguments['nullable'];
                    }
                    if (isset($arguments['type']) === true) {
                        $column->type = $arguments['type'];
                    }
                    if (isset($arguments['length']) === true) {
                        $column->size = $arguments['length'];
                    }
                    if (isset($arguments['default']) === true) {
                        $column->default = $arguments['default'];
                    }
                    if (isset($arguments['name']) === true) {
                        $column->name = $arguments['name'];
                    }
                    if (isset($arguments['index']) === true) {
                        $column->index = $arguments['index'];
                    }
                    if (isset($arguments['primary']) === true) {
                        $column->primary = $arguments['primary'];
                    }
                    if (isset($arguments['index']) === true) {
                        $column->index = $arguments['index'];
                    }
                    if (isset($arguments['autoIncrement']) === true) {
                        $column->autoIncrement = $arguments['autoIncrement'];
                    }
                    if (isset($arguments['unique']) === true) {
                        $column->unique = $arguments['unique'];
                    }
                }
            }

            if ($column->name === null) {
                $column->name = $column->property;
            }

            $data->column[$column->property] = $column;
        }

        return $data;
    }

    private static function __setup(Arr $data)
    {
        self::$database = $data->database;
        self::$table    = $data->table;

        if ($data->column->length === 0) {
            throw new \Exception('Model need at least one column.');
        }

        self::$column = $data->column;
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
        if (isset($this->{$key}) === true)
            return $this->{$key};

        return null;
    }

    private static function __parse(Arr $column, mixed $value = null) : mixed
    {
        // TODO: parse data
        return $value;
    }
}