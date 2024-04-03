<?php

namespace Flames;

class Server
{
    public static function getOS()
    {
        return strtolower(PHP_OS_FAMILY);
    }

    public static function isUnix()
    {
        return (!self::isWindows());
    }

    public static function isWindows()
    {
        return (self::getOS() === 'windows');
    }
}