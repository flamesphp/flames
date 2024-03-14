<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Controller\Response;
use Flames\Kernel\Route;
use Flames\Route\Client;

/**
 * @internal
 */
final class Kernel
{
    public const VERSION = '1.0.18';
    public const MODULE  = 'SERVER';

    public static function run() : void
    {
        self::setup();

        if (self::dispatchEvents() === false) {
            // TODO: 404
        }

        self::shutdown();
    }

    protected static function setup() : void
    {
        ob_start();
        define('START_TIME', microtime(true));
        define('ROOT_PATH', realpath(__DIR__ . '/../') . '/');
        define('APP_PATH', ROOT_PATH . 'App/');

        require(ROOT_PATH . 'Flames/AutoLoad.php');
        AutoLoad::run();

        mb_internal_encoding('UTF-8');
        Required::file(ROOT_PATH . 'Flames/Kernel/Wrapper/Raw.php');

        self::setEnvironment();
        self::setDumpper();
    }

    protected static function setEnvironment() : void
    {
        $environment = new Environment();
        $environment->inject();
    }

    protected static function setDumpper() : void
    {
        $environment = Environment::default();
        if ($environment->DUMP_ENABLED === true) {
//            Required::file(ROOT_PATH . '.fork/_Flames/Sage/Sage.php');
            ThirdParty\Sage\Sage::$theme = ThirdParty\Sage\Sage::THEME_SOLARIZED_DARK;
            ThirdParty\Sage\Sage::$editor = $environment->DUMP_IDE;
            Required::file(ROOT_PATH . 'Flames/ThirdParty/Sage/Register.php');
        }
        else {
            Required::file(ROOT_PATH . 'Flames/ThirdParty/Sage/Plain.php');
        }
    }

    protected static function dispatchEvents() : bool
    {
        if (Event::dispatch('Initialize', 'onInitialize') === false) {
            return false;
        }

        if (CLI::isCLI() === false && str_starts_with($_SERVER['REQUEST_URI'], '/.flames')) {
            if (Client::run($_SERVER['REQUEST_URI']) !== false) {
                return true;
            }
        }

        $router = Event::dispatch('Route', 'onRoute', new Router());
        if ($router !== null) {
            $match = $router->getMatch();
            if ($match === null) {
                return false;
            }

            return self::dispatchRoute($match);
        }

        return false;
    }

    protected static function dispatchRoute($routeData) : bool
    {
        $requestData = Route::mountRequestData($routeData);
        $requestDataAllow = Event::dispatch('Route', 'onMatch', $requestData);
        if ($requestDataAllow === false) {
            return false;
        }

        $controller = new $routeData->controller();
        $response = $controller->{$routeData->delegate}($requestData);

        if (($response instanceof Response) === false) {
            $response = new Response($response);
        }

        $output = $response->output;

        $_output = Event::dispatch('Output', 'onOutput', $requestData, $output);
        if ($_output !== null) {
            $output = (string)$_output;
        }

        self::sendHeaders($response->headers, $response->code);
        echo $output;

        return true;
    }

    protected static function sendHeaders(Arr|null $headers, int $code)
    {
        header('X-Powered-By: Flames');
        http_response_code($code);

        if ($headers !== null) {
            foreach ($headers as $key => $value) {
                header($key . ':' . $value);
            }
        }
    }

    protected static function shutdown()
    {
        $runTime = microtime(true) - constant('START_TIME');
//        dump($runTime);
    }
}