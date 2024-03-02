<?php

namespace Flames;

/**
 * @internal
 */
class Event
{
    public static function dispatch(mixed $event, mixed $action = null, $params = null, $paramsExtra = null) : Router|bool|RequestData|string|null
    {
        $event = (string)$event;
        $action = (string)$action;

        $path = (ROOT_PATH . 'App/Server/Event/' . $event . '.php');
        if (file_exists($path) === true) {
            $instance = new ('\App\Server\Event\\' . $event)();
            if ($action === null)
                return $instance;
            return $instance->{$action}($params, $paramsExtra);
        }

        return null;
    }
}