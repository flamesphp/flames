<?php

namespace Flames;

use Flames\Collection\Arr;

class Cookie
{
    public static function set(string $key, mixed $value, ?int $expire = null): void
    {
        if ($expire === null) {
            $expire = (time() + 30758400);
        }

        setcookie($key, $value, $expire, '/');
    }

    public static function get(string $key): string|null
    {
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        }
        return null;
    }

    public static function getAll(): Arr
    {
        return Arr($_COOKIE);
    }
}