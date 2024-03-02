<?php

namespace Flames;

use _Flames\Bgaze\Dotenv\Helpers;
use Flames\Collection\Arr;

/**
 * Description for the class
 * @property bool $DUMP_ENABLED
 * @property string $DUMP_IDE
 * @property string $DUMP_THEME
 */
final class Environment
{
    protected static Arr|null $dataGlobal = null;
    protected static Environment|null $dataDefault = null;

    protected Arr|null $data = null;

    public function __construct(mixed $path = null)
    {
        $path = (string)$path;

        if (empty($path) === true) {
            $path = (ROOT_PATH . 'env/php.env');
        }

        $this->load($path);
        if (self::$dataDefault === null) {
            self::$dataDefault = $this;
        }
    }

    public function __get(mixed $key) : mixed
    {
        $key = (string)$key;
        if (empty($key) === true) {
            return null;
        }

        if ($this->data->containsKey($key)) {
            return $this->data[$key];
        }

        return null;
    }

    public function __set(mixed $key, mixed $value) : void
    {
        $key = (string)$key;
        if (empty($key) === true) {
            return;
        }

        $this->data[$key] = $value;
        return;
    }

    public static function default() : Environment|null
    {
        return self::$dataDefault;
    }

    public static function get($key) : mixed
    {
        $key = (string)$key;
        if (empty($key) === true) {
            return null;
        }

        if (@self::$dataGlobal->containsKey($key)) {
            return self::$dataGlobal[$key];
        }

        return null;
    }

    public static function set(mixed $key, mixed $value) : void
    {
        $key = (string)$key;
        if (empty($key) === true) {
            return;
        }

        if (self::$dataGlobal === null) {
            return;
        }

        self::$dataGlobal[$key] = $value;
        return;
    }

    public function inject()
    {
        if (self::$dataGlobal === null) {
            self::$dataGlobal = Arr();
        }

        foreach ($this->data as $key => $value) {
            self::$dataGlobal[$key] = $value;
        }
    }

    protected function load(mixed $path) : void
    {
        $basePath  = (ROOT_PATH . '.cache/environment/');
        $cachePath = ($basePath . sha1($path));
        $currentTime = filemtime($path);

        if (file_exists($cachePath) === true && filemtime($cachePath) === $currentTime) {
            $this->data = unserialize(file_get_contents($cachePath));
            return;
        }

        $this->data = Arr(Helpers::fromFile($path));
        $success = @file_put_contents($cachePath, serialize($this->data));
        if ($success === false) {
            if (is_dir($basePath) === false) {
                mkdir($basePath, 0777, true);
                @file_put_contents($cachePath, serialize($this->data));
            }
        }
        @touch($cachePath, $currentTime);
    }
}