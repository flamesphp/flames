<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Collection\Strings;

class Router
{
    protected Arr|null $routes = null;

    public function __construct()
    {
        $this->routes = Arr();
    }

    public function add(mixed $method = 'GET', mixed $route = '/', mixed $controller = null, mixed $delegateString = 'onRequest')
    {
        $route          = (string)$route;
        $method         = (string)$method;
        $controller     = (string)$controller;
        $delegateString = (string)$delegateString;

        $routeData = Arr();

        while (Strings::contains($route, ' ') === true) {
            $route = Strings::remove($route, ' ');
        }

        $routeData->controller = $controller;
        if (Strings::isEmpty($routeData->controller) === true) {
            return null;
        }

        $routeData->routeFormatted = $route;
        $routeData->parameters     = Arr();
        $routeData->methods        = $method;
        $routeData->delegate       = $delegateString;

        $routeParsed = ('' . $route);

        while (Strings::indexOf($routeParsed,'{{') !== false) {
            $initIndexOf  = Strings::indexOf($routeParsed,'{{');
            $extract      = Strings::sub($routeParsed, $initIndexOf + 2);
            $closeIndexOf = Strings::indexOf($extract, '}}');

            if ($closeIndexOf === null) {
                break;
            }
            $extract = Strings::sub($extract, 0, $closeIndexOf);
            if (Strings::contains($extract, '{{') === true || Strings::contains($extract,'}}') === true) {
                break;
            }

            $routeParsed = (Strings::sub($routeParsed, 0, $initIndexOf) .
                Strings::sub($routeParsed, $initIndexOf + $closeIndexOf + 4));
            $routeData->parameters->add($extract);
        }

        // Case insensitive
        if ($routeData->parameters->count > 0) {
            $routeCaseInsensitive = ('' . $routeData->routeFormatted);
            for ($i = 0; $i < $routeData->parameters->count; $i++) {
                $routeCaseInsensitive = Strings::replace($routeCaseInsensitive, '{{' . $routeData->parameters[$i] . '}}', '{{%parameter' . $i . '%}}');
            }

            $routeCaseInsensitive = Strings::toLower($routeCaseInsensitive);
            for ($i = 0; $i < $routeData->parameters->count; $i++) {
                $routeCaseInsensitive = Strings::replace($routeCaseInsensitive, '{{%parameter' . $i . '%}}', '{{' . $routeData->parameters[$i] . '}}');
            }
        } else  {
            $routeData->routeFormatted = Strings::toLower($routeData->routeFormatted);
        }

        $this->routes->add($routeData);
    }

    public function getMatch()
    {
        if (CLI::isCLI() === false) {
            return self::getMatchWeb();
        }

        return $this->getMatchCLI();
    }

    protected function getMatchWeb()
    {
        // Mount router
        $router = new \_Flames\AltoRouter();

        $paramItems = Arr();
        for ($i = 0; $i < $this->routes->count; $i++) {
            $route = ($this->routes[$i]);
            if ($route->methods === 'CLI') {
                continue;
            }
            $routeParsed = $route->routeFormatted;

            foreach ($route->parameters as $param) {
                $paramItems->add($param);
                $routeParsed = Strings::replace($routeParsed,
                    '{{' . $param . '}}',
                    '[*:item' . $paramItems->count . 'item]');
            }

            $router->map($route->methods, $routeParsed, null, $i);
        }

        // Try get
        $match = $router->match();

        if ($match === false || $match === null) {
            return null;
        }

        $route = ($this->routes[($match['name'])]);
        $parameters = Arr();
        $matchParameters = Arr($match['params']);

        foreach ($route->parameters as $param) {
            $encodedItem = null;
            for ($i = 0; $i < $paramItems->count; $i++)
                if ($paramItems[$i] == $param) {
                    $break = false;
                    $encodedItem = ('item' . ($i + 1) . 'item');

                    foreach ($matchParameters as $_paramEnc => $value)
                        if ($encodedItem == $_paramEnc) {
                            $parameters[$param] = $value;
                            $break = true;
                            break;
                        }

                    if ($break == true)
                        break;
                }
        }

        // Parse parameters values to case sensitive
        $currentUrl = $_SERVER['REQUEST_URI'];
        $currentUrlLower = Strings::toLower($currentUrl);
        $caseSensitiveParameters = Arr();

        foreach ($parameters as $key => $parameter) {
            $valid = false;
            $indexOfInit = Strings::indexOf($currentUrlLower, $parameter);
            if ($indexOfInit !== null) {
                $extractInit = Strings::sub($currentUrl, $indexOfInit);
                $extractFinish = Strings::sub($extractInit, 0, -1);
                if (Strings::toLower($extractFinish) == $parameter) {
                    $caseSensitiveParameters[$key] = $extractFinish;
                    $valid = true;
                }
            }
            if ($valid === false) {
                $caseSensitiveParameters[$key] = $parameter;
            }
        }

        return Arr([
            'url'        => $currentUrl,
            'command'    => null,
            'controller' => $route->controller,
            'parameters' => $caseSensitiveParameters,
            'delegate'   => $route->delegate
        ]);
    }

    protected function getMatchCLI()
    {
        $args = $_SERVER['argv'];
        if (count($args) === 1) {
            return null;
        }

        $command = $args[1];

        // Match
        foreach ($this->routes as $route) {
            if ($route->methods !== 'CLI') {
                continue;
            }

            if ($route->routeFormatted === $command) {
                return Arr([
                    'url'        => null,
                    'command'    => $command,
                    'controller' => $route->controller,
                    'parameters' => $route->parameters,
                    'delegate'   => $route->delegate
                ]);
            }
        }

        return null;
    }
}