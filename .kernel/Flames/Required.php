<?php

namespace Flames;

class Required
{
    public static function _function(string $name)
    {
        if (\function_exists($name) === false) {
            $path = (ROOT_PATH . '.kernel/.polyfill/' . $name . '.php');
            require $path;
        }
    }

    public static function file(string $path, $includeRootFullPath = false)
    {
        if ($includeRootFullPath === true) {
            $path = (ROOT_PATH . $path);
        }

        require $path;
    }
}