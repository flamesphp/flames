<?php

namespace App\Server\Event;

class Load extends \Flames\Event\Load
{
    public function onLoad(string $name): bool
    {
        return parent::onLoad($name);
    }
}