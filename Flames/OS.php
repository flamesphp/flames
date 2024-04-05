<?php


namespace Flames;

/**
 * The OS class provides methods for determining the operating system.
 */
class OS
{
    /**
     * Returns the name of the operating system.
     *
     * @return string The name of the operating system.
     */
    public static function getName() : string
    {
        return strtolower(PHP_OS_FAMILY);
    }

    /**
     * Checks if the current operating system is Unix-like.
     *
     * @return bool Returns true if the current operating system is Unix-like, false otherwise.
     */
    public static function isUnix() : bool
    {
        return (!self::isWindows());
    }

    /**
     * Checks if the current operating system is Windows.
     *
     * @return bool Returns true if the current operating system is Windows, false otherwise.
     */
    public static function isWindows() : bool
    {
        return (self::getName() === 'windows');
    }
}