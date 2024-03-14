<?php

namespace Flames\Event\Client;

use Flames\RequestData;
use Flames\Router;

abstract class Route
{
    public function onRoute(Router $router) : Router|null
    {
        return $router;
    }

    public function onMatch(RequestData $requestData) : bool
    {
        return true;
    }
}