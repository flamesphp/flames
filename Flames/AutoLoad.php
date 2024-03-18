<?php

namespace Flames;

/**
 * @internal
 */
final class AutoLoad
{
    public static bool $event = false;

    public static function run(): void
    {
        // Verify if event load exists and register
        $path = (ROOT_PATH . 'App/Server/Event/Load.php');
        if (file_exists($path) === true) {
            self::$event = true;
        }

        \spl_autoload_register(function ($name) {
            self::onLoad($name);
        });
    }

    protected static function onLoad(string $name): void
    {
        // Case Flames Internal
        if (str_starts_with($name, 'Flames\\')) {
            $name = str_replace('\\', '/', $name);
            $path = (ROOT_PATH . $name . '.php');
            require $path;
            return;
        }

        // Case App
        elseif (str_starts_with($name, 'App\\')) {
            $fileName = str_replace('\\', '/', $name);
            $path = (ROOT_PATH . $fileName . '.php');
            require $path;

            if (method_exists($name, '__constructStatic') === true) {
                ($name . '::__constructStatic')();
            }
            return;
        }

        elseif (self::$event === true) {
            Event::dispatch('Load', 'onLoad', $name);
        }
    }
}