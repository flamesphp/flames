<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\ORM\Database;
use Flames\ORM\Table;

abstract class Model
{
    private const __VERSION__ = 1;

    private static string|null $database = null;
    private static string|null $table    = null;

    public static function __constructStatic()
    {
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
            return;
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
    }

    private static function __getReflection(string $class) : Arr
    {
        $data = Arr([
            'version' => self::__VERSION__,
            'class'   => Arr()
        ]);

        $reflection = new \ReflectionClass($class);
        $attributes = $reflection->getAttributes();
        foreach($attributes as $attribute) {
            $data->class[] = Arr([
                'name'      => $attribute->getName(),
                'arguments' => Arr($attribute->getArguments()),
                'target'    => $attribute->getTarget(),
            ]);
        }

        return $data;
    }

    private static function __setup(Arr $data)
    {
        foreach ($data->class as $attribute) {
            if ($attribute->name === Database::class) {
                if (isset($attribute->arguments->name) === true) {
                    self::$database = $attribute->arguments->name;
                }
            }
            elseif ($attribute->name === Table::class) {
                if (isset($attribute->arguments->name) === true) {
                    self::$table = $attribute->arguments->name;
                }
            }
        }
    }

    protected static function getTable() : string
    {
        return self::$table;
    }

    protected static function setTable(string $table) : bool
    {
        if (empty($table) === true)
            return false;

        self::$table = $table;
        return true;
    }

    protected static function getDatabase() : string
    {
        return self::$database;
    }

    protected static function setDatabase(string $database) : bool
    {
        if (empty($database) === true)
            return false;

        self::$database = $database;
        return true;
    }
}