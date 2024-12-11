<?php

namespace Flames\Collection;

/**
 * The Floats class provides various methods for manipulating and working with integers.
 */
final class Floats
{
    /**
     * Converts a given value to a int representation.
     *
     * @param mixed $value The value to be converted.
     *
     * @return float The float representation of the given value.
     */
    public static function parse(mixed $value)
    {
        return (float)$value;
    }
}