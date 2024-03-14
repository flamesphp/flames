<?php

namespace Flames\Kernel;

use Flames\Collection\Arr;
use Flames\JS;
use Flames\Router;

/**
 * @internal
 */
final class Client
{
    public static function run() : void
    {
        self::setup();
    }

    protected static function setup() : void
    {
        self::simulateGlobals();
        self::dispatchEvents();
    }
    protected static function simulateGlobals() : void
    {
        $location = JS::getWindow()->location;
        $origin = $location->origin;
        $_SERVER['REQUEST_URI'] = substr($location->href, strlen($origin));
    }

    protected static function dispatchEvents() : bool
    {
        if (class_exists('\\App\\Client\\Event\\Route') === true) {
            $route = new \App\Client\Event\Route();
            $router = $route->onRoute(new Router());
            if ($router !== null) {
                $match = $router->getMatch();
                if ($match === null) {
                    return false;
                }

                return self::dispatchRoute($match, $route);
            }
        }

        return false;
    }

    protected static function dispatchRoute($routeData, $route) : bool
    {
        $requestData = Route::mountRequestData($routeData);

        $requestDataAllow = $route->onMatch($requestData);
        if ($requestDataAllow === false) {
            return false;
        }

        $controller = new $routeData->controller();
        $controller->{$routeData->delegate}($requestData);

        return true;
    }
}