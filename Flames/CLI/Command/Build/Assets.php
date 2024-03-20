<?php

namespace Flames\CLI\Command\Build;

use Flames\Kernel\Client\Build;

class Assets
{
    public function run()
    {
        $build = new Build();
        $build->run(true);
    }
}