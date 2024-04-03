<?php

namespace Flames\CLI\Command;

use Flames\Command;
use Flames\Environment;

/**
 * @internal
 */
class Install
{
    protected bool $debug = false;

    protected bool $withKeyGenerate = true;
    protected bool $withCryptographyKeyGenerate = true;
    protected bool $withExample = true;

    public function __construct($data)
    {
        $this->withKeyGenerate = (!$data->option->contains('nokey'));
        $this->withCryptographyKeyGenerate = (!$data->option->contains('nocryptographykey'));
        $this->withExample = (!$data->option->contains('noexample'));
    }

    public function run(bool $debug = false) : bool
    {
        $default = Environment::default();
        if ($default->isValid() === false) {
            $envPath = (ROOT_PATH . '.env');
            $envDistPath = ($envPath . '.dist');
            copy($envDistPath, $envPath);
        }

        if ($this->withKeyGenerate === true) {
            Command::run('key:generate');
        }
        if ($this->withCryptographyKeyGenerate === true) {
            Command::run('cryptography:key:generate');
        }
        if ($this->withExample === true) {
            // TODO: make example project
//            Command::run('install:example');
        }

        return true;
    }
}