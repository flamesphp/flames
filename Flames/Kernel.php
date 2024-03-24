<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Controller\Response;
use Flames\Kernel\Client\Build;
use Flames\Kernel\Route;
use Flames\Router\Client;
use Flames\Router;

/**
 * @internal
 */
final class Kernel
{
    public const VERSION = '1.0.18';
    public const MODULE  = 'SERVER';

    protected static Router|null $defaultRouter = null;

    public static function run() : void
    {
        self::setup();


        $dispatchCLI = false;
        if (self::dispatchEvents() === false) {

            // TODO: 404
            if (CLI::isCLI() === true) {
                self::dispatchCLI();
                $dispatchCLI = true;
            }
        }

        if ($dispatchCLI === false && CLI::isCLI() === true) {
            self::dispatchCLI();
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
        if (CLI::isCLI() === false) {
            self::setErrorHandler();
        }
        self::setDumpper();
    }

    protected static function setEnvironment() : void
    {
        $environment = new Environment();
        $environment->inject();
    }

    protected static function setErrorHandler() : void
    {
        $environment = Environment::default();
        if ($environment->ERROR_HANDLER_ENABLED === true) {
            $errorHandler = new ErrorHandler\Run;
            $pageHandler = new ErrorHandler\Handler\PrettyPageHandler();
            $pageHandler->setEditor($environment->ERROR_HANDLER_IDE);
            $errorHandler->pushHandler($pageHandler);
            $errorHandler->register();
        }
    }

    protected static function setDumpper() : void
    {
        $environment = Environment::default();
        if ($environment->DUMP_ENABLED === true) {
//            Required::file(ROOT_PATH . '.fork/_Flames/Dump/Dump.php');
            Dump\Dump::$theme = Dump\Dump::THEME_SOLARIZED_DARK;
            Dump\Dump::$editor = $environment->DUMP_IDE;
            Required::file(ROOT_PATH . 'Flames/Dump/Register.php');
        }
        else {
            Required::file(ROOT_PATH . 'Flames/Dump/Plain.php');
        }
    }

    protected static function dispatchEvents() : bool
    {
        Header::set('X-Powered-By', 'Flames');

        if (Event::dispatch('Initialize', 'onInitialize') === false) {
            return false;
        }

        if (CLI::isCLI() === false && str_starts_with($_SERVER['REQUEST_URI'], '/.flames')) {
            if (Client::run($_SERVER['REQUEST_URI']) !== false) {
                return true;
            }
        }

        self::$defaultRouter = Event::dispatch('Route', 'onRoute', new Router());
        if (self::$defaultRouter !== null) {
            $match = self::$defaultRouter->getMatch();
            if ($match === null) {
                return false;
            }

            return self::dispatchRoute($match);
        }

        return false;
    }

    protected static function dispatchRoute($routeData) : bool
    {
        $requestData = Route::mountRequestData($routeData, Connection::getIp());
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

    protected static function dispatchCLI()
    {
        $system = new CLI\System();
        $system->run();
    }

    protected static function sendHeaders(Arr|null $headers, int $code)
    {
        Header::set('Code', $code);

        if ($headers !== null) {
            foreach ($headers as $key => $value) {
                Header::set($key, $value);
            }
        }

        Header::send();
    }

    protected static function shutdown()
    {
        $runTime = microtime(true) - constant('START_TIME');
//        dump($runTime);
    }

    public static function getDefaultRouter() : Router|null
    {
        return self::$defaultRouter;
    }
}