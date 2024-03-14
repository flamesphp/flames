<?php

namespace Flames;

final class CLI
{
    public static function isCLI() : bool
    {
        return (Kernel::MODULE === 'SERVER' && $_SERVER['SCRIPT_FILENAME'] === 'cli');
    }
}