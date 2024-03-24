<?php

namespace Flames;

use Flames\Collection\Arr;

class Header
{
    protected static $data = [];

    public static function set(string $key, mixed $value)
    {
        $value = (string)$value;
        self::$data[$key] = $value;
    }

    public static function get(string $key) : mixed
    {
        if (isset(self::$data[$key]) === true) {
            return self::$data[$key];
        }

        return null;
    }

    public static function getAll() : Arr
    {
        return Arr(self::$data);
    }

    public static function clear()
    {
        self::$data = [];
    }

    public static function send()
    {
        if (array_key_exists('Code', self::$data) === true) {
            http_response_code(self::$data['Code']);
        }
        elseif (array_key_exists('code', self::$data) === true) {
            http_response_code(self::$data['code']);
        }

        foreach (self::$data as $key => $value) {
            if (strtolower($key) === 'code') {
                continue;
            }
            header($key . ':' . $value);
        }
    }
}