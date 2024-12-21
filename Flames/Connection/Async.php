<?php

namespace Flames\Connection;


class Async
{
    public static function isAsync(): bool
    {
        $headers = (function_exists('getallheaders') ? getallheaders() : null);

        if (isset($headers['X-Flames-Request']) === true && $headers['X-Flames-Request'] === 'async') {
            return true;
        }
        if (isset($headers['x-flames-request']) === true && $headers['x-flames-request'] === 'async') {
            return true;
        }

        return false;
    }
}