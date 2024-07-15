<?php

namespace Flames;

/**
 * The Event class handles the dispatching of events.
 *
 * @internal
 */
final class Event
{
    /**
     * Dispatches an event and executes the corresponding action.
     *
     * @param mixed $event The name of the event to dispatch.
     * @param mixed $action (optional) The name of the action to execute. Default is null.
     * @param mixed $params (optional) Additional parameters to pass to the action. Default is null.
     * @param mixed $paramsExtra (optional) Extra parameters to pass to the action. Default is null.
     * @return Router|RequestData|bool|string|null Returns an instance of Router, RequestDatam, boolean value,
     *   string, or null based on the dispatched event and executed action.
     */
    public static function dispatch(mixed $event, mixed $action = null, $params = null, $paramsExtra = null) : Router|RequestData|bool|string|null
    {
        $event = (string)$event;
        $action = (string)$action;

        $path = (APP_PATH . 'Server/Event/' . $event . '.php');
        if (file_exists($path) === true) {
            $instance = new ('\App\Server\Event\\' . $event)();
            if ($action === null)
                return $instance;
            return $instance->{$action}($params, $paramsExtra);
        }

        return null;
    }
}