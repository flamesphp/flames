<?php

namespace Flames;

use Exception;
use Vrzno;

class JS
{
    protected static $window = null;

    public static function eval(string $code) : mixed
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        } else {
            return self::getWindow()->eval($code);
        }
    }

    public static function getWindow() : Vrzno|null
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        } else {
            if (self::$window === null) {
                self::$window = new Vrzno();
            }
            return self::$window;
        }
    }
}