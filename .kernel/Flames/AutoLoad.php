<?php

namespace Flames;

/**
 * @internal
 */
final class AutoLoad
{
    public static function run()
    {
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
                $name = str_replace('\\', '/', $name);
                $path = (ROOT_PATH . $name . '.php');
                require $path;
                return;
            }

            // Case Fork
            elseif (str_starts_with($name, '_Flames\\')) {
                $name = str_replace('\\', '/', $name);
                $path = (KERNEL_PATH . '.fork/' . $name . '.php');
                require $path;
                return;
            }

            // TODO: failed page
        });
    }
}