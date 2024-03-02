<?php

namespace Flames\Event;

abstract class Initialize
{
    public function onInitialize() : bool
    {
        return true;
    }
}