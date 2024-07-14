<?php

namespace Flames\Collection;

/**
 * The Ints class provides various methods for manipulating and working with integers.
 */
final class Ints
{
    /**
     * Converts a given value to a int representation.
     *
     * @param mixed $value The value to be converted.
     *
     * @return bool The bool representation of the given value.
     */
    public static function parse(mixed $value)
    {
        return (int)$value;
    }
}