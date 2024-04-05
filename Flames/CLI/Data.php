<?php

namespace Flames\CLI;

use Flames\Collection\Arr;

/**
 * Class Data
 *
 * The Data class is responsible for processing command line arguments and returning the data in a structured format.
 *
 * @internal
 */
final class Data
{
    /**
     * Retrieves data from the given array of arguments or from $_SERVER['argv'] if no arguments are provided.
     *
     * @param array|null $args The array of arguments (default: null).
     *
     * @return Arr The data retrieved from the arguments.
     */
    public static function getData(array $args = null) : Arr
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