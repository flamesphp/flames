<?php

namespace Flames;

use Exception;
use Vrzno;

class JS
{
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
            return new Vrzno();
        }
    }
}