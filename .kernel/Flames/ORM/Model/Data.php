<?php

namespace Flames\ORM\Model;

use Flames\Collection\Arr;

/**
 * @internal
 */
class Data
{
    private const __VERSION__ = 10;

    public static function mountData(string $class) : Arr
    {
        $path = (ROOT_PATH . str_replace('\\', '/', $class) . '.php');

        $basePath  = (ROOT_PATH . '.cache/model/');
        $cachePath = ($basePath . sha1($class));
        $currentTime = filemtime($path);

        if (file_exists($cachePath) === true && filemtime($cachePath) === $currentTime) {
            $data = unserialize(file_get_contents($cachePath));
            if ($data->version === self::__VERSION__) {
                return $data;
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
        return $data;
    }

    private static function __getReflection(string $class) : Arr
    {
        $data = Arr([
            'version'  => self::__VERSION__,
            'class'    => $class,
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
                if ($attribute->getName() === \Flames\ORM\Column::class) {
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

            if ($column->primary === true && $column->index === true) {
                throw new \Exception('Property ' . $column->property . ' on class ' . $data->class . ' can\'t be primary-key and index together.');
            }
            if ($column->primary === true && $column->unique === true) {
                throw new \Exception('Property ' . $column->property . ' on class ' . $data->class . ' can\'t be primary-key and unique together.');
            }
            if ($column->index === true && $column->unique === true) {
                throw new \Exception('Property ' . $column->property . ' on class ' . $data->class . ' can\'t be index and unique together.');
            }

            $data->column[$column->property] = $column;
        }

        return $data;
    }
}
