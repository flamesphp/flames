<?php

namespace Flames\Coroutine\Timeout;

/**
 * @internal
 */
class Event
{
    public static array $delegatesData = [];

    public static function onDispatch($uid)
    {
        if (isset(self::$delegatesData[$uid]) === false) {
            return;
        }

        $delegateData = self::$delegatesData[$uid];
        $delegate = $delegateData->delegate;
        $args = $delegateData->args;
        while (count($args) < 16) {
            $args[] = null;
        }

        $delegate($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10], $args[11], $args[12], $args[13], $args[14], $args[15]);
    }
}