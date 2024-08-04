<?php

namespace Flames\Kernel\Client;

use Flames\Connection;
use Flames\Element;
use Flames\Event\Element\Click;
use Flames\Event\Element\Change;
use Flames\Event\Element\Input;
use Flames\Js;
use Flames\Kernel\Route;
use Flames\Router;

/**
 * @internal
 */
final class Dispatch
{
    protected static $instances = null;
    public static function run() : void
    {
        self::clean();
        self::setup();
    }

    protected static function clean()
    {
        if (self::$instances !== null) {
            foreach (self::$instances as &$instance) {
                unset($instance);
            }
        }
        self::$instances = [];
    }

    protected static function setup() : void
    {
        self::simulateGlobals();
        self::dispatchEvents();
    }
    protected static function simulateGlobals() : void
    {
        $location = Js::getWindow()->location;
        $origin = $location->origin;
        $_SERVER['REQUEST_URI'] = explode('#', substr($location->href, strlen($origin)))[0];
    }

    protected static function dispatchEvents() : bool
    {
        \Flames\Kernel::__injector();

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
        $requestData = Route::mountRequestData($routeData, Connection::getIp());

        $requestDataAllow = $route->onMatch($requestData);
        if ($requestDataAllow === false) {
            return false;
        }

        $controller = new $routeData->controller();
        self::$instances[$routeData->controller] = $controller;
        $controller->{$routeData->delegate}($requestData);

        return true;
    }

    public static function getInstance(string $class) : mixed
    {
        if (isset(self::$instances[$class]) === true) {
            return self::$instances[$class];
        } else {
            self::$instances[$class] = new $class();
            return self::$instances[$class];
        }
    }

    public static function onClick(string $class, string $method, string $uid)
    {
        $click = new Click(Element::query($uid));
        $instance = self::getInstance($class);
        $instance->{$method}($click);
    }

    public static function onChange(string $class, string $method, string $uid)
    {
        $change = new Change(Element::query($uid));
        $instance = self::getInstance($class);
        $instance->{$method}($change);
    }

    public static function onInput(string $class, string $method, string $uid)
    {
        $input = new Input(Element::query($uid));
        $instance = self::getInstance($class);
        $instance->{$method}($input);
    }
}