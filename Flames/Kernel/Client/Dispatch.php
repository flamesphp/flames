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
        self::dispatchHooks();
        self::dispatchEvents();
    }
    protected static function simulateGlobals() : void
    {
        $location = Js::getWindow()->location;
        $origin = $location->origin;
        $_SERVER['REQUEST_URI'] = explode('#', substr($location->href, strlen($origin)))[0];
    }

    protected static function dispatchHooks()
    {
        $window = Js::getWindow();
        $elements = Element::queryAll('*');
        foreach ($elements as $element) {

            $clickUid = $element->getAttribute('@click');
            if ($clickUid !== null) {
                foreach ($window->Flames->Internal->eventTriggers as $eventTrigger) {
                    if ($clickUid === $eventTrigger->uid && $eventTrigger->type === 'click') {
                        $element->event->click(function($event) use ($eventTrigger) {
                            $instance = self::getInstance($eventTrigger->class);
                            $instance->{$eventTrigger->name}($event);
                        });
                        break;
                    }
                }
            }

            $changeUid = $element->getAttribute('@change');
            if ($changeUid !== null) {
                foreach ($window->Flames->Internal->eventTriggers as $eventTrigger) {
                    if ($changeUid === $eventTrigger->uid && $eventTrigger->type === 'change') {
                        $element->event->change(function($event) use ($eventTrigger) {
                            $instance = self::getInstance($eventTrigger->class);
                            $instance->{$eventTrigger->name}($event);
                        });
                        break;
                    }
                }
            }

            $inputUid = $element->getAttribute('@change');
            if ($inputUid !== null) {
                foreach ($window->Flames->Internal->eventTriggers as $eventTrigger) {
                    if ($inputUid === $eventTrigger->uid && $eventTrigger->type === 'input') {
                        $element->event->input(function($event) use ($eventTrigger) {
                            $instance = self::getInstance($eventTrigger->class);
                            $instance->{$eventTrigger->name}($event);
                        });
                        break;
                    }
                }
            }
        }
    }

    protected static function dispatchEvents() : bool
    {
        \Flames\Kernel::__injector();

        if (class_exists('\\App\\Client\\Event\\Ready') === true) {
            $ready = new \App\Client\Event\Ready();
            $ready->onReady();
        }

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
}