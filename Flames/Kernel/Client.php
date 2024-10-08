<?php

namespace Flames\Kernel;

use Flames\Js;

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
            self::$data = unserialize(base64_decode(Js::getWindow()->eval('document.querySelector(\'flames\').innerHTML')));
            self::$getData = true;
            Js::eval('document.querySelector(\'flames\').remove();');
        }
        return self::$data;
    }

    public static function __injectData($data)
    {
        self::$data = unserialize(base64_decode($data));
        self::$getData = true;
    }

    public static function __injector()
    {
        self::__getData();
    }

    public static function __loader()
    {
        Js::eval('Flames.Internal.executeStaticConstructor();');
        \Flames\Kernel\Client\Dispatch::run();
    }
}