<?php

namespace Flames;

class Required
{
    public static function _function(string $name) : string|null
    {
        if (\function_exists($name) === false) {
            $path = (ROOT_PATH . 'Flames/Kernel/Polyfill/' . $name . '.php');

            $error = null;
            try {
                @require $path;
            } catch (\Error $e) {
                $error = $e->getMessage();

                try {
                    @require (ROOT_PATH . 'App/Server/Polyfill/' . $name . '.php');
                    $error = null;
                } catch (\Error $e) {
                    $error = $e->getMessage();
                }
            }

            if ($error !== null) {
                throw new \Error($error);
            }

            return $name;
        }

        return $name;
    }

    public static function file(string $path, $includeRootFullPath = false)
    {
        if ($includeRootFullPath === true) {
            $path = (ROOT_PATH . $path);
        }

        require $path;
    }
}