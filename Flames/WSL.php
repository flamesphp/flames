<?php

namespace Flames;

/**
 * WSL class provides a static method to determine whether the code is running within a Windows Subsystem Linux.
 */
class WSL
{
    private static bool|null $isWSL = null;
    private static bool|null $isWSL2 = null;

    /**
     * Determines whether the code is running within a Windows Subsystem Linux.
     *
     * @return bool Returns true if the code is running within a Windows Subsystem Linux, false otherwise.
     */
    public static function isWSL() : bool
    {
        if (self::$isWSL !== null) {
            return self::$isWSL;
        }

        if (OS::isUnix() === false) {
            self::$isWSL = false;
            return self::$isWSL;
        }

        $hostname = shell_exec('hostnamectl');
        if ($hostname !== null) {
            $hostname = strtolower($hostname);

            if (str_contains($hostname, 'virtualization: wsl') === true) {
                self::$isWSL = true;
                return self::$isWSL;
            }
        }

        self::$isWSL = self::detectWSL2($hostname);
        return self::$isWSL;
    }

    /**
     * Determines whether the code is running within a Windows Subsystem Linux v2.
     *
     * @return bool Returns true if the code is running within a Windows Subsystem Linux v2, false otherwise.
     */
    public static function isWSL2() : bool
    {
        if (self::$isWSL2 !== null) {
            return self::$isWSL2;
        }

        if (OS::isUnix() === false) {
            self::$isWSL2 = false;
            return self::$isWSL2;
        }

        $hostname = shell_exec('hostnamectl');
        if ($hostname !== null) {
            $hostname = strtolower($hostname);
        }

        self::$isWSL2 = self::detectWSL2($hostname);
        return self::$isWSL2;
    }

    /**
     * Internal determines whether the code is running within a Windows Subsystem Linux v2.
     *
     * @return bool Returns true if the code is running within a Windows Subsystem Linux v2, false otherwise.
     */
    protected static function detectWSL2(string|null $hostname) : bool
    {
        if ($hostname === null) {
            return false;
        }

        $split = explode("\n", $hostname);
        foreach ($split as $line) {
            if (str_contains($line, 'kernel:') === true) {
                $line = trim($line);
                if (str_ends_with($line, '-wsl2') === true) {
                    return true;
                }
                break;
            }
        }

        return false;
    }
}