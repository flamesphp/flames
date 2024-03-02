<?php

namespace App\Server\Event;

class Initialize extends \Flames\Event\Initialize
{
    public function onInitialize(): bool
    {
        return parent::onInitialize();
    }
}