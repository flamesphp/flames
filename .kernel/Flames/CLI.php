<?php

namespace Flames;

final class CLI
{
    public static function isCLI() : bool
    {
        return ($_SERVER['SCRIPT_FILENAME'] === 'cli');
    }
}