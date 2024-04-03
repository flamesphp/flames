<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Environment\Helpers;

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
    protected bool $valid = true;
    protected string|null $path;

    public function __construct(mixed $path = null)
    {
        $path = (string)$path;

        if (empty($path) === true) {
            $path = (ROOT_PATH . '.env');
        }

        $this->path = $path;
        $this->load();
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

    public function getAll() : Arr
    {
        return $this->data;
    }

    public function toArray() : array
    {
        return (array)$this->data;
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

    public function save() : void
    {
        $data = str_replace(["\r\n", "\r"], "\n", file_get_contents($this->path));
        $mount = '';

        $lines = explode("\n", $data);
        foreach ($lines as $line) {
            if (str_starts_with($line, "\n") || str_starts_with($line, '#') || str_contains($line, '=') === false) {
                $mount .= ($line . "\n");
                continue;
            }

            $var = trim(explode('=', $line)[0]);
            $value = $this->{$var};
            if ($value === true) {
                $value = 'true';
            } elseif ($value === false) {
                $value = 'false';
            }
            $mount .= ($var . '=' . $value . "\n");
        }

        @file_put_contents($this->path, $mount);
    }

    public function isValid() : bool
    {
        return $this->valid;
    }

    protected function load() : void
    {
        $basePath  = (ROOT_PATH . '.cache/environment/');
        $cachePath = ($basePath . sha1($this->path));

        if (file_exists($this->path) === false) {
            $this->valid = false;
            $this->data = Arr();
            return;
        }

        $currentTime = filemtime($this->path);

        if (file_exists($cachePath) === true && filemtime($cachePath) === $currentTime) {
            $this->data = unserialize(file_get_contents($cachePath));
            return;
        }

        $this->data = Arr(Helpers::fromFile($this->path));
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