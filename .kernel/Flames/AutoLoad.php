<?php

namespace Flames;

/**
 * @internal
 */
final class AutoLoad
{
    public static $event = false;

    public static function run()
    {
        // Verify if event load exists and register
        $path = (ROOT_PATH . 'App/Server/Event/Load.php');
        if (file_exists($path) === true) {
            self::$event = true;
        }

        \spl_autoload_register(function ($name) {
            // Case Flames Internal
            if (str_starts_with($name, 'Flames\\')) {
                $name = str_replace('\\', '/', $name);
                $path = (KERNEL_PATH . $name . '.php');
                require $path;
                return;
            }

            // Case App
            elseif (str_starts_with($name, 'App\\')) {
                $fileName = str_replace('\\', '/', $name);
                $path = (ROOT_PATH . $fileName . '.php');
                require $path;

                if (method_exists($name,'__constructStatic') === true) {
                    ($name . '::__constructStatic')();
                }
                return;
            }

            // Case Fork
            elseif (str_starts_with($name, '_Flames\\')) {
                $name = str_replace('\\', '/', $name);
                $path = (KERNEL_PATH . '.fork/' . $name . '.php');
                require $path;
                return;
            }

            elseif (self::$event === true) {
                Event::dispatch('Load', 'onLoad', $name);
            }
        });
    }
}