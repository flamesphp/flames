<?php


namespace Flames;

class Server
{
    public static function getUri($withQueryString = false): string
    {
        if ($withQueryString === true) {
            return $_SERVER['REQUEST_URI'];
        }

        return explode('?', $_SERVER['REQUEST_URI'])[0];
    }
}