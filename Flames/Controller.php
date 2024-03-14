<?php

namespace Flames;

use Flames\Collection\Arr;
use Flames\Controller\Response;
use Flames\Controller\Data;

abstract class Controller
{
    public function onRequest(RequestData $requestData) : Response|string
    {
        return $this->success();
    }

    public function success(Arr|array|string $data = null, int $code = 200, Arr|array|null $headers = null) : Response|string
    {
        if (is_string($data)) {

        } else {
            if (is_array($data) === true) {
                $data = Arr($data);
            }

            $method = self::__getCaller();
            if ($method !== null) {
                if (static::$__methods->containsKey($method)) {
                    $view = new View();
                    $view->addView(static::$__methods[$method]->path);
                    return new Response($view->render($data), $data, $code, $headers);
                }
            }

            return new Response(null, $data, $code, $headers);
        }

        return '';
    }

    public function error(Arr|array|string $data = null, int $code = 200, Arr|array|null $headers = null) : Response|string
    {
        return $this->success($data, 500, $headers);
    }

    private static bool $__setup = false;
    private static $__methods = null;
    public static function __constructStatic(): void
    {
        if (self::$__setup === true) {
            return;
        }

        self::__setup(Data::mountData(static::class));
        self::$__setup = true;
    }

    private static function __setup(Arr $data): void
    {
        self::$__methods = $data->methods;
    }

    private static function __getCaller()
    {
        $controllerClass = static::class;
        $lastFunc = null;

        $debugBacktrace = debug_backtrace();
        foreach ($debugBacktrace as $_debugBacktrace) {
            if ($_debugBacktrace['class'] === $controllerClass) {
                $lastFunc = $_debugBacktrace['function'];
            }
        }

        return $lastFunc;
    }

}