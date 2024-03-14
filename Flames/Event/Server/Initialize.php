<?php

namespace Flames\Event\Server;

abstract class Initialize
{
    public function onInitialize() : bool
    {
        return true;
    }
}