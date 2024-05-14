<?php

namespace Flames;

use Flames\Collection\Arr;

/**
 * Class Header represents a utility class for managing HTTP headers.
 */
class Header
{
    protected static $data = [];

    /**
     * Sets the value for a given key in the header array.
     *
     * @param string $key The key to set the value for.
     * @param mixed $value The value to be set.
     *
     * @return void
     */
    public static function set(string $key, mixed $value)
    {
        $value = (string)$value;
        self::$data[$key] = $value;
    }

    /**
     * Retrieves the value associated with the given key from the header array.
     *
     * @param string $key The key of the value to retrieve.
     *
     * @return mixed|null The value associated with the key, or null if the key does not exist in the header array.
     */
    public static function get(string $key) : mixed
    {
        if (isset(self::$data[$key]) === true) {
            return self::$data[$key];
        }

        return null;
    }

    /**
     * Retrieves all items from the data array.
     *
     * @return Arr The array containing all items.
     */
    public static function getAll() : Arr
    {
        return Arr(self::$data);
    }

    /**
     * Clears the data array.
     *
     * @return void
     */
    public static function clear() : void
    {
        self::$data = [];
    }

    /**
     * Sends HTTP response headers based on the data array.
     *
     * Then it iterates through each key-value pair in the header array
     * and sends the HTTP response header by concatenating the key and value with a colon separator.
     *
     * @return void
     */
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

    public static function redirect(string $url)
    {
        header('Location: ' . $url);
        return Arr(['redirect' => $url]);
    }
}