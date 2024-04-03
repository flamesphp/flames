<?php

namespace Flames\CLI\Command\Key;

use Flames\Command;
use Flames\Cryptography\Hash;
use Flames\Environment;

/**
 * @internal
 */
class Generate
{
    const BASE_PATH = (ROOT_PATH . 'App/Client/Resource/');


    protected bool $debug = false;

    public function run(bool $debug = false) : bool
    {
        $environment = Environment::default();
        if ($environment->isValid() === false) {
            Command::run('install --nokey --noexample');
            $environment = Environment::default();
        }

        $environment->APPLICATION_KEY = Hash::getRandom();
        $environment->save();

        return true;
    }
}