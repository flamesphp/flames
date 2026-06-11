<?php
declare(strict_types=1);


namespace Flames\Interfaces\Event;

interface Initialize
{
    public function onInitialize(): bool;
}
