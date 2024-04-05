<?php

/**
 * Class JS
 *
 * The JS class provides methods to evaluate JavaScript code on the client-side.
 */

namespace Flames;

use Exception;
use Vrzno;

/**
 * Class JS
 *
 * The JS class provides utility methods for executing JavaScript code
 * on the client-side.
 */
class JS
{
    protected static $window = null;

    /**
     * Evaluate JavaScript code on the client-side.
     *
     * @param string $code The JavaScript code to be evaluated.
     *
     * @return mixed The result of the evaluation.
     *
     * @throws Exception If the method is called on the server-side.
     */
    public static function eval(string $code) : mixed
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        } else {
            return self::getWindow()->eval($code);
        }
    }

    /**
     * Returns the window object.
     *
     * @return WindowNativeJS|null The window object if it exists, otherwise null.
     *
     * @throws Exception If the method is called on the server module.
     */
    public static function getWindow() : Vrzno|null
    {
        if (Kernel::MODULE === 'SERVER') {
            throw new Exception('Method only works on client.');
        } else {
            if (self::$window === null) {
                self::$window = new Vrzno();
            }
            return self::$window;
        }
    }
}
