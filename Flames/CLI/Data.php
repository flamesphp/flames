<?php

namespace Flames\CLI;

/**
 * @internal
 */
class Data
{
    public static function getData(array $args = null)
    {
        if ($args === null) {
            $args = $_SERVER['argv'];
        }

        $data = Arr([
            'command'   => null,
            'argument'  => Arr(),
            'option'    => Arr(),
            'parameter' => Arr()
        ]);

        unset($args[0]);
        if (isset($args[1]) === true) {
            $data->command = $args[1];
            unset($args[1]);
        }

        foreach ($args as $arg) {
            if (str_starts_with($arg, '-') === false) {
                $data->argument[] = $arg;
                continue;
            }
            if (str_starts_with($arg, '--') === true) {
                $data->option[] = substr($arg, 2);
                continue;
            }

            $arg = substr($arg, 1);
            $split = explode('=', $arg);
            if (count($split) === 1) {
                $split[1] = null;
            }
            $data->parameter[$split[0]] = $split[1];
        }

        return $data;
    }
}