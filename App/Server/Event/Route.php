<?php

namespace App\Server\Event;

use App\Server\Controller\HelloWorld;
use App\Server\Controller\Test;
use Flames\RequestData;
use Flames\Router;

class Route extends \Flames\Event\Route
{
    public function onRoute(Router $router) : Router|null
    {
        $router->add('GET', '/', HelloWorld::class, 'onRequest');
        $router->add('GET', '/test', Test::class, 'onRequest');

        return $router;
    }

    public function onMatch(RequestData $requestData) : bool
    {
        return parent::onMatch($requestData);
    }
}