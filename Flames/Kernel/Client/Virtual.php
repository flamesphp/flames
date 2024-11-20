<?php

namespace Flames\Kernel\Client
{
    /**
     * @internal
     */
    final class Virtual
    {
        private static $buffers = [];

        private static $constructors = [];

        public static function load(string $class): bool
        {
            $classHash = sha1($class);
            if (isset(self::$buffers[$classHash]) === true) {
                eval(base64_decode(self::$buffers[$classHash]));
                unset(self::$buffers[$classHash]);
                if (in_array($classHash, self::$constructors) === true) {
                    $class::__constructStatic();
                }
                return true;
            }

            return false;
        }
    }
}