<?php

namespace Flames\Kernel;

/**
 * @internal
 */
final class Client
{
    public const VERSION = '1.0.18';
    public const MODULE  = 'CLIENT';

    private static $data = null;
    private static $getData = false;
    public static function __getData() {
        if (self::$getData === false) {
            self::$data = unserialize(base64_decode(JS::getWindow()->eval('document.querySelector(\'flames\').innerHTML')));
            self::$getData = true;
            JS::getWindow()->eval('document.querySelector(\'flames\').remove();');
        }
        return self::$data;
    }
}