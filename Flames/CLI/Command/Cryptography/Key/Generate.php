<?php

namespace Flames\CLI\Command\Cryptography\Key;

use Flames\Command;
use Flames\Cryptography\Hash;
use Flames\Environment;

/**
 * Class Generate
 *
 * The Generate class is responsible for generating the cryptography key for the environment.
 *
 * @internal
 */
final class Generate
{
    protected bool $debug = false;

    /**
     * Run the key generation.
     *
     * @param bool $debug Set to true if debug mode is enabled, false otherwise.
     * @return bool Returns true if the application runs successfully, false otherwise.
     */
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