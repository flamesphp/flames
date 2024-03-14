<?php

namespace Flames\Event\Server;

abstract class Load
{
    public function onLoad(string $name) : bool
    {
        return false;
    }
}