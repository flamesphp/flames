<?php
declare(strict_types=1);


namespace Flames\Interfaces\Event;

use Flames\Framework\Controller\RequestData;

interface Route
{
    public function onRoute(): void;

    public function onMatch(RequestData $requestData): bool;
}
