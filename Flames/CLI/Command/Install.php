<?php

namespace Flames\CLI\Command;

use Flames\Environment;

/**
 * @internal
 */
class Install
{
    protected bool $debug = false;

    public function run(bool $debug = false) : bool
    {
        $default = Environment::default();
        if ($default->isValid() === false) {
            $envPath = (ROOT_PATH . '.env');
            $envDistPath = ($envPath . '.dist');
            copy($envDistPath, $envPath);
        }

        return true;
    }
}