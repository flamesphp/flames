<?php

namespace Flames\CLI\Command\Cryptography\Key;

use Flames\Command;
use Flames\Cryptography\Hash;
use Flames\Environment;

/**
 * @internal
 */
class Generate
{
    protected bool $debug = false;

    public function run(bool $debug = false) : bool
    {
        $environment = Environment::default();
        if ($environment->isValid() === false) {
            Command::run('install --nokey --nocryptographykey --noexample');
            $environment = Environment::default();
        }

        $environment->CRYPTOGRAPHY_KEY = Hash::getRandom();
        $environment->save();

        return true;
    }
}