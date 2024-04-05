<?php

namespace Flames;

class OS
{
    public static function getName() : string
    {
        return strtolower(PHP_OS_FAMILY);
    }

    public static function isUnix() : bool
    {
        return (!self::isWindows());
    }

    public static function isWindows() : bool
    {
        return (self::getName() === 'windows');
    }
}