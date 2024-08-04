<?php

namespace Flames\Kernel\Client
{
    /**
     * @internal
     */
    final class Virtual
    {
        private static $buffers = [];

        public static function load(string $class): bool
        {
            $classHash = sha1($class);
            if (isset(self::$buffers[$classHash]) === true) {
                eval(base64_decode(self::$buffers[$classHash]));
                return true;
            }

            return false;
        }
    }
}
