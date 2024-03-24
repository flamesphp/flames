<?php

namespace Flames;

use Flames\CLI\Data;
use Flames\CLI\System;

class Command
{
    public static function run(string $command, bool $debug = false) : bool
    {
        $args = ['bin'];

        $_args = explode(' ', $command);
        foreach ($_args as $arg) {
            $args[] = $arg;
        }
        $system = new System(Data::getData($args), $debug);
        return $system->run();
    }
}