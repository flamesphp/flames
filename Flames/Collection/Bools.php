<?php

namespace Flames\Collection;

/**
 * The Bools class provides various methods for manipulating and working with booleans.
 */
final class Bools
{
    /**
     * Converts a given value to a bool representation.
     *
     * @param mixed $value The value to be converted.
     *
     * @return bool The bool representation of the given value.
     */
    public static function parse(mixed $value)
    {
        if (is_bool($value) === true) {
            return $value;
        }

        if (is_string($value) === true) {
            if ($value === 'true') {
                return true;
            }
            elseif ($value === 'false') {
                return false;
            }
            elseif ($value === '1') {
                return true;
            }
            elseif ($value === '0') {
                return false;
            }
            elseif ($value === '-1') {
                return null;
            }
            elseif ($value === 'yes') {
                return true;
            }
            elseif ($value === 'no') {
                return false;
            }

            $value = strtolower($value);
            if ($value === 'true') {
                return true;
            }
            elseif ($value === 'false') {
                return false;
            }
            elseif ($value === 'yes') {
                return true;
            }
            elseif ($value === 'no') {
                return false;
            }
        }

        if (is_int($value) === true) {
            if ($value === 1) {
                return true;
            }
            elseif ($value === 0) {
                return false;
            }
        }

        return null;
    }
}