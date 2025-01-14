<?php

namespace Flames\Collection;

/**
 * The Strings class provides various methods for manipulating and working with strings.
 */
final class Strings
{
    /**
     * Converts a given value to a string representation.
     *
     * @param mixed $value The value to be converted.
     *
     * @return string The string representation of the given value.
     */
    public static function parse(mixed $value)
    {
        return (string)$value;
    }

    /**
     * Calculates the length of a given value.
     *
     * @param mixed $value The value to calculate the length for.
     * @param bool $multibyte Indicates whether to calculate the length using multibyte encoding. Default is false.
     *
     * @return int The length of the value.
     */
    public static function length(mixed $value, bool $multibyte = false) : int
    {
        $value = (string)$value;

        if ($multibyte === true) {
            return mb_strlen($value, 'UTF-8');
        }

        return strlen($value);
    }

    /**
     * Counts the number of characters in a given value.
     *
     * @param mixed $value The value to count the characters for.
     * @param bool $multibyte Indicates whether to count the characters using multibyte encoding. Default is false.
     *
     * @return int The number of characters in the value.
     */
    public static function count(mixed $value, bool $multibyte = false) : int
    {
        $value = (string)$value;
        return self::length($value, $multibyte);
    }

    /**
     * Converts a given value to lowercase.
     *
     * @param mixed $value The value to convert to lowercase.
     * @param bool $multibyte Indicates whether to use multibyte encoding for the conversion. Default is false.
     *
     * @return string The value converted to lowercase.
     */
    public static function toLower(mixed $value, bool $multibyte = false) : string
    {
        $value = (string)$value;

        if ($multibyte === true) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    /**
     * Converts a given value to uppercase.
     *
     * @param mixed $value The value to convert to uppercase.
     * @param bool $multibyte Indicates whether to convert the value using multibyte encoding. Default is false.
     *
     * @return string The value converted to uppercase.
     */
    public static function toUpper(mixed $value, bool $multibyte = false) : string
    {
        $value = (string)$value;

        if ($multibyte === true) {
            return mb_strtoupper($value, 'UTF-8');
        }

        return strtoupper($value);
    }

    /**
     * Checks if a given value starts with a specified substring.
     *
     * @param mixed $value The value to check if it starts with the substring.
     * @param mixed $needle The substring to check if it is at the beginning of the value.
     * @param bool $caseSensitive Optional. Indicates whether the comparison should be case-sensitive. Default is true.
     *
     * @return bool True if the value starts with the substring, false otherwise.
     */
    public static function startsWith(mixed $value, mixed $needle, bool $caseSensitive = true) : bool
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $value = self::toLower($value, true);
            $needle = self::toLower($value, true);
            return str_starts_with($value, $needle);
        }

        return str_starts_with($value, $needle);
    }

    /**
     * Checks if a given value ends with a specified substring.
     *
     * @param mixed $value The value to check.
     * @param mixed $needle The substring to look for.
     * @param bool $caseSensitive Indicates whether the comparison is case-sensitive. Default is true.
     *
     * @return bool True if the value ends with the specified substring, false otherwise.
     */
    public static function endsWith(mixed $value, mixed $needle, bool $caseSensitive = true) : bool
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $value = self::toLower($value, true);
            $needle = self::toLower($value, true);
            return str_ends_with($value, $needle);
        }

        return str_ends_with($value, $needle);
    }

    /**
     * Checks whether a given value contains a specified needle.
     *
     * @param mixed $value The value to check for containment.
     * @param mixed $needle The value to search for within the value.
     * @param bool $caseSensitive Indicates whether the search should be case-sensitive. Default is true.
     *
     * @return bool Returns true if the value contains the needle, otherwise false.
     */
    public static function contains(mixed $value, mixed $needle, bool $caseSensitive = true) : bool
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $value = self::toLower($value, true);
            $needle = self::toLower($value, true);
            return str_contains($value, $needle);
        }

        return str_contains($value, $needle);
    }

    /**
     * Checks if a given value contains any of the elements in the array.
     *
     * @param mixed $value The value to check.
     * @param mixed $array The array containing the elements to search for.
     * @param bool $caseSensitive Indicates whether the search is case-sensitive. Default is true.
     *
     * @return bool True if the value contains any of the array elements, false otherwise.
     */
    public static function containsAny(mixed $value, mixed $array, bool $caseSensitive = true) : bool
    {
        $value = (string)$value;
        if ($caseSensitive === true) {
            $value = self::toLower($value, true);
        }

        if (is_array($array) || $array instanceof Arr) {
            if ($caseSensitive === true) {
                foreach ($array as $needle) {
                    $needle = self::toLower($needle, true);
                    if (str_contains($value, $needle) === true) {
                        return true;
                    }
                }

                return false;
            }

            foreach ($array as $needle) {
                if (str_contains($value, $needle) === true) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if a given value is equal to a needle.
     *
     * @param mixed $value The value to check equality for.
     * @param mixed $needle The needle to compare against.
     * @param bool $caseSensitive Indicates whether the comparison should be case sensitive or not. Default is true.
     *
     * @return bool True if the value is equal to the needle, false otherwise.
     */
    public static function equals(mixed $value, mixed $needle, bool $caseSensitive = true) : bool
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $value = self::toLower($value, true);
            $needle = self::toLower($value, true);
            return ($value === $needle);
        }

        return ($value === $needle);
    }

    /**
     * Checks if a value equals any of the values in an array.
     *
     * @param mixed $value The value to check.
     * @param mixed $array The array of values to compare to.
     * @param bool $caseSensitive Indicates whether the comparison should be case sensitive. Default is true.
     *
     * @return bool True if the value equals any of the values in the array, false otherwise.
     */
    public static function equalsAny(mixed $value, mixed $array, bool $caseSensitive = true) : bool
    {
        $value = (string)$value;
        if ($caseSensitive === true) {
            $value = self::toLower($value, true);
        }

        if (is_array($array) || $array instanceof Arr) {
            if ($caseSensitive === true) {
                foreach ($array as $needle) {
                    $needle = self::toLower($needle, true);
                    if ($value === $needle) {
                        return true;
                    }
                }

                return false;
            }

            foreach ($array as $needle) {
                if ($value === $needle) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks if a given value is empty.
     *
     * @param mixed $value The value to check for emptiness.
     *
     * @return bool Returns true if the value is empty, false otherwise.
     */
    public static function isEmpty(mixed $value) : bool
    {
        $value = (string)$value;
        return empty($value);
    }

    /**
     * Replaces all occurrences of a substring in a string with another substring.
     *
     * @param mixed $value The input string where the replacements will be made.
     * @param mixed $needle The substring to be replaced.
     * @param mixed $replace The replacement substring.
     *
     * @return string The resulting string after all replacements.
     */
    public static function replace(mixed $value, mixed $needle, mixed $replace) : string
    {
        $value   = (string)$value;
        $needle  = (string)$needle;
        $replace = (string)$replace;

        return str_replace($needle, $replace, $value);
    }

    /**
     * Removes all occurrences of a given substring from a string.
     *
     * @param mixed $value The input string to remove occurrences from.
     * @param mixed $needle The substring to remove from the input string.
     *
     * @return string The input string with all occurrences of the substring removed.
     */
    public static function remove(mixed $value, mixed $needle) : string
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        return str_replace($needle, '', $value);
    }

    /**
     * Encodes a given value.
     *
     * @param mixed $value The value to encode.
     * @param bool $raw Indicates whether to use raw encoding. Default is false.
     *
     * @return string The encoded value.
     */
    public static function encode(mixed $value, bool $raw = false) : string
    {
        $value = (string)$value;

        if ($raw === false) {
            return rawurlencode($value);
        }

        return urlencode($value);
    }

    /**
     * Decodes a given value.
     *
     * @param mixed $value The value to decode.
     * @param bool $raw Indicates whether to decode using rawurldecode(). Default is false.
     *
     * @return string The decoded value.
     */
    public static function decode(mixed $value, bool $raw = false) : string
    {
        $value = (string)$value;

        if ($raw === false) {
            return rawurldecode($value);
        }

        return urldecode($value);
    }

    /**
     * Splits a string into an array based on a given delimiter.
     *
     * @param mixed $value The value to be splitted.
     * @param string $needle The delimiter used to split the value. Default is ','.
     * @param bool $clearEmpty Indicates whether to remove empty elements from the resulting array. Default is true.
     * @param bool $keepDelimiter Indicates whether to keep the delimiter as a separate element in the array. Default is false.
     *
     * @return Arr An array containing the splitted elements.
     */
    public static function split(mixed $value, string $needle = ',', bool $clearEmpty = true, bool $keepDelimiter = false) : Arr
    {
        $value = (string)$value;

        $split = (($keepDelimiter === false)
            ? explode($needle, $value)
            : @preg_split('@(?=' . $needle . ')@', $value)
        );

        $return = \Arr();
        foreach ($split as &$value)
            if ($clearEmpty !== true || $value !== '') {
                $return[] = $value;
            }

        return $return;
    }

    /**
     * Splits a given value into substrings of a specified length.
     *
     * @param mixed $value The value to split.
     * @param mixed $length The length of each substring.
     *
     * @return Arr An array containing the substrings of the given value.
     */
    public static function splitLength(mixed $value, mixed $length) : Arr
    {
        $value  = (string)$value;
        $length = (int)$length;

        $split = Arr();
        while (strlen($value) > $length) {
            $split[] = substr($value, 0, $length);
            $value = substr($value, $length);
        }

        return $split;
    }

    /**
     * Splits a given value into an array of words.
     *
     * @param mixed $value The value to split into words.
     *
     * @return Arr An array containing the words from the value.
     */
    public static function splitWords(mixed $value) : Arr
    {
        $value  = (string)$value;
        return Arr(explode(' ', $value));
    }

    /**
     * Splits a given value into an array of lines.
     *
     * @param mixed $value The value to split into lines.
     *
     * @return Arr An array containing the lines of the value.
     */
    public static function splitLines(mixed $value): Arr
    {
        $value  = (string)$value;

        $value = str_replace(["\r\n", "\r"], ["\n", "\n"], $value);
        return Arr(explode("\n", $value));
    }

    /**
     * Retrieves a substring from a given value.
     *
     * @param mixed $value The value to extract the substring from.
     * @param mixed $start The starting position of the substring.
     * @param mixed|null $length The length of the substring, optional. Default is null.
     *
     * @return string The extracted substring.
     */
    public static function sub(mixed $value, mixed $start, mixed $length = null) : string
    {
        $value = (string)$value;
        $start = (int)$start;

        if ($length !== null) {
            $length = (int)$length;
        }

        return substr($value, $start, $length);
    }

    /**
     * Returns the index of the first occurrence of a substring in a given value.
     *
     * @param mixed $value The value to search in.
     * @param mixed $needle The substring to search for.
     * @param bool $caseSensitive Indicates whether the search should be case sensitive. Default is true.
     *
     * @return int|null The index of the first occurrence of the substring, or null if not found.
     */
    public static function indexOf(mixed $value, mixed $needle, bool $caseSensitive = true) : int|null
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $return = stripos($value, $needle);
            if ($return === false) {
                $return = null;
            }
            return $return;
        }

        $return = strpos($value, $needle);
        if ($return === false) {
            $return = null;
        }

        return $return;
    }

    /**
     * Returns the last occurrence of a substring in a string, or null if not found.
     *
     * @param mixed $value The string to search in.
     * @param mixed $needle The string to search for.
     * @param bool $caseSensitive Determines whether the search is case-sensitive. Default is true.
     *
     * @return int|null The index of the last occurrence of the substring, or null if not found.
     */
    public static function lastIndexOf(mixed $value, mixed $needle, bool $caseSensitive = true) : int|null
    {
        $value  = (string)$value;
        $needle = (string)$needle;

        if ($caseSensitive === false) {
            $return = strripos($value, $needle);
            if ($return === false) {
                $return = null;
            }

            return $return;
        }

        $return = strrpos($value, $needle);
        if ($return === false) {
            $return = null;
        }

        return $return;
    }

    /**
     * Trims specified characters from the beginning and end of a string.
     *
     * @param mixed $value The string to be trimmed.
     * @param mixed $charList The characters to be trimmed. Default is null (trim whitespace characters).
     * @param bool $multibyte Indicates whether to use multibyte encoding for trimming. Default is false.
     *
     * @return string The trimmed string.
     */
    public static function trim(mixed $value, mixed $charList = null, bool $multibyte = false) : string
    {
        $value = (string)$value;

        if ($charList !== null) {
            $charList = (string)$charList;
        } else {
            return \trim($value);
        }

        if ($multibyte === true) {
            $charlist = \str_replace('/', '\/', preg_quote($charList));
            return \preg_replace("/(^[$charlist]+)|([$charlist]+$)/us", '', $value);
        }

        return trim($value, $charList);
    }

    /**
     * Adds slashes to a given value.
     *
     * @param mixed $value The value to add slashes to.
     *
     * @return string The value with slashes added.
     */
    public static function addSlashes(mixed $value) : string
    {
        $value = (string)$value;
        return addslashes($value);
    }

    /**
     * Removes slashes from a given value.
     *
     * @param mixed $value The value to remove slashes from.
     *
     * @return string The value with slashes removed.
     */
    public static function removeSlashes(mixed $value) : string
    {
        $value = (string)$value;
        return stripslashes($value);
    }

    /**
     * Converts a given value to a base64 encoded string.
     *
     * @param mixed $value The value to convert to base64 encoding.
     *
     * @return string The base64 encoded value.
     */
    public static function toBase64(mixed $value) : string
    {
        $value = (string)$value;
        return base64_encode($value);
    }

    /**
     * Decodes a value from Base64 encoding.
     *
     * @param mixed $value The value to decode from Base64.
     *
     * @return string|null The decoded value, or null if decoding fails.
     */
    public static function fromBase64(mixed $value) : string|null
    {
        $value = (string)$value;

        $return = base64_decode($value);
        if ($return === false) {
            $return = null;
        }

        // TODO: verify data:

        return $return;
    }

    /**
     * Retrieves only the numbers from a given value.
     *
     * @param mixed $value The value to extract numbers from.
     * @param mixed $whiteList Characters or numbers that should not be removed from the value. Default is an empty string.
     *
     * @return string The extracted numbers from the value.
     */
    public static function getOnlyNumbers(mixed $value, mixed $whiteList = '') : string
    {
        $value     = (string)$value;
        $whiteList = (string)$whiteList;

        return preg_replace("/[^0-9" . $whiteList . "]*/", '', $value);
    }

    /**
     * Removes all non-letter characters from a given value.
     *
     * @param mixed $value The value to remove non-letter characters from.
     *
     * @return string The value with all non-letter characters removed.
     */
    public static function getOnlyLetters(mixed $value) : string
    {
        $value = (string)$value;
        return preg_replace('/[^a-zA-Z]+/', '', $value);
    }

    /**
     * Removes all non-letter and non-number characters from a given value.
     *
     * @param mixed $value The value to remove non-letter and non-number characters from.
     *
     * @return string The value with all non-letter and non-number characters removed.
     */
    public static function getOnlyLettersAndNumbers(mixed $value) : string
    {
        $value = (string)$value;
        return preg_replace('/[^a-zA-Z0-9]+/', '', $value);
    }

    /**
     * Limits the length of a given value.
     *
     * @param mixed $value The value to limit.
     * @param mixed $limit The maximum length to limit the value to. Default is 10.
     *
     * @return string The limited value.
     */
    public static function limit(mixed $value, mixed $limit = 10) : string
    {
        $value = (string)$value;
        $limit = (int)$limit;

        return substr($value, 0, $limit);
    }

    /**
     * Generates a random string with a specified length.
     *
     * @param int $length The length of the random string to generate. Default is 32.
     *
     * @return string The random string.
     */
    public static function getRandom(int $length = 32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // TODO: to UTF-8
    // function toUTF8

    /**
     * Removes accents from a string value.
     *
     * @param mixed $value The value to remove accents from.
     *
     * @return string The value with accents removed.
     */
    public static function removeAccents(mixed $value) : string
    {
        $string = (string)$value;

        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if (self::__seems_utf8($string)) {
            $chars = array(
                // Decompositions for Latin-1 Supplement
                chr(194) . chr(170) => 'a', chr(194) . chr(186) => 'o',
                chr(195) . chr(128) => 'A', chr(195) . chr(129) => 'A',
                chr(195) . chr(130) => 'A', chr(195) . chr(131) => 'A',
                chr(195) . chr(132) => 'A', chr(195) . chr(133) => 'A',
                chr(195) . chr(134) => 'AE', chr(195) . chr(135) => 'C',
                chr(195) . chr(136) => 'E', chr(195) . chr(137) => 'E',
                chr(195) . chr(138) => 'E', chr(195) . chr(139) => 'E',
                chr(195) . chr(140) => 'I', chr(195) . chr(141) => 'I',
                chr(195) . chr(142) => 'I', chr(195) . chr(143) => 'I',
                chr(195) . chr(144) => 'D', chr(195) . chr(145) => 'N',
                chr(195) . chr(146) => 'O', chr(195) . chr(147) => 'O',
                chr(195) . chr(148) => 'O', chr(195) . chr(149) => 'O',
                chr(195) . chr(150) => 'O', chr(195) . chr(153) => 'U',
                chr(195) . chr(154) => 'U', chr(195) . chr(155) => 'U',
                chr(195) . chr(156) => 'U', chr(195) . chr(157) => 'Y',
                chr(195) . chr(158) => 'TH', chr(195) . chr(159) => 's',
                chr(195) . chr(160) => 'a', chr(195) . chr(161) => 'a',
                chr(195) . chr(162) => 'a', chr(195) . chr(163) => 'a',
                chr(195) . chr(164) => 'a', chr(195) . chr(165) => 'a',
                chr(195) . chr(166) => 'ae', chr(195) . chr(167) => 'c',
                chr(195) . chr(168) => 'e', chr(195) . chr(169) => 'e',
                chr(195) . chr(170) => 'e', chr(195) . chr(171) => 'e',
                chr(195) . chr(172) => 'i', chr(195) . chr(173) => 'i',
                chr(195) . chr(174) => 'i', chr(195) . chr(175) => 'i',
                chr(195) . chr(176) => 'd', chr(195) . chr(177) => 'n',
                chr(195) . chr(178) => 'o', chr(195) . chr(179) => 'o',
                chr(195) . chr(180) => 'o', chr(195) . chr(181) => 'o',
                chr(195) . chr(182) => 'o', chr(195) . chr(184) => 'o',
                chr(195) . chr(185) => 'u', chr(195) . chr(186) => 'u',
                chr(195) . chr(187) => 'u', chr(195) . chr(188) => 'u',
                chr(195) . chr(189) => 'y', chr(195) . chr(190) => 'th',
                chr(195) . chr(191) => 'y', chr(195) . chr(152) => 'O',
                // Decompositions for Latin Extended-A
                chr(196) . chr(128) => 'A', chr(196) . chr(129) => 'a',
                chr(196) . chr(130) => 'A', chr(196) . chr(131) => 'a',
                chr(196) . chr(132) => 'A', chr(196) . chr(133) => 'a',
                chr(196) . chr(134) => 'C', chr(196) . chr(135) => 'c',
                chr(196) . chr(136) => 'C', chr(196) . chr(137) => 'c',
                chr(196) . chr(138) => 'C', chr(196) . chr(139) => 'c',
                chr(196) . chr(140) => 'C', chr(196) . chr(141) => 'c',
                chr(196) . chr(142) => 'D', chr(196) . chr(143) => 'd',
                chr(196) . chr(144) => 'D', chr(196) . chr(145) => 'd',
                chr(196) . chr(146) => 'E', chr(196) . chr(147) => 'e',
                chr(196) . chr(148) => 'E', chr(196) . chr(149) => 'e',
                chr(196) . chr(150) => 'E', chr(196) . chr(151) => 'e',
                chr(196) . chr(152) => 'E', chr(196) . chr(153) => 'e',
                chr(196) . chr(154) => 'E', chr(196) . chr(155) => 'e',
                chr(196) . chr(156) => 'G', chr(196) . chr(157) => 'g',
                chr(196) . chr(158) => 'G', chr(196) . chr(159) => 'g',
                chr(196) . chr(160) => 'G', chr(196) . chr(161) => 'g',
                chr(196) . chr(162) => 'G', chr(196) . chr(163) => 'g',
                chr(196) . chr(164) => 'H', chr(196) . chr(165) => 'h',
                chr(196) . chr(166) => 'H', chr(196) . chr(167) => 'h',
                chr(196) . chr(168) => 'I', chr(196) . chr(169) => 'i',
                chr(196) . chr(170) => 'I', chr(196) . chr(171) => 'i',
                chr(196) . chr(172) => 'I', chr(196) . chr(173) => 'i',
                chr(196) . chr(174) => 'I', chr(196) . chr(175) => 'i',
                chr(196) . chr(176) => 'I', chr(196) . chr(177) => 'i',
                chr(196) . chr(178) => 'IJ', chr(196) . chr(179) => 'ij',
                chr(196) . chr(180) => 'J', chr(196) . chr(181) => 'j',
                chr(196) . chr(182) => 'K', chr(196) . chr(183) => 'k',
                chr(196) . chr(184) => 'k', chr(196) . chr(185) => 'L',
                chr(196) . chr(186) => 'l', chr(196) . chr(187) => 'L',
                chr(196) . chr(188) => 'l', chr(196) . chr(189) => 'L',
                chr(196) . chr(190) => 'l', chr(196) . chr(191) => 'L',
                chr(197) . chr(128) => 'l', chr(197) . chr(129) => 'L',
                chr(197) . chr(130) => 'l', chr(197) . chr(131) => 'N',
                chr(197) . chr(132) => 'n', chr(197) . chr(133) => 'N',
                chr(197) . chr(134) => 'n', chr(197) . chr(135) => 'N',
                chr(197) . chr(136) => 'n', chr(197) . chr(137) => 'N',
                chr(197) . chr(138) => 'n', chr(197) . chr(139) => 'N',
                chr(197) . chr(140) => 'O', chr(197) . chr(141) => 'o',
                chr(197) . chr(142) => 'O', chr(197) . chr(143) => 'o',
                chr(197) . chr(144) => 'O', chr(197) . chr(145) => 'o',
                chr(197) . chr(146) => 'OE', chr(197) . chr(147) => 'oe',
                chr(197) . chr(148) => 'R', chr(197) . chr(149) => 'r',
                chr(197) . chr(150) => 'R', chr(197) . chr(151) => 'r',
                chr(197) . chr(152) => 'R', chr(197) . chr(153) => 'r',
                chr(197) . chr(154) => 'S', chr(197) . chr(155) => 's',
                chr(197) . chr(156) => 'S', chr(197) . chr(157) => 's',
                chr(197) . chr(158) => 'S', chr(197) . chr(159) => 's',
                chr(197) . chr(160) => 'S', chr(197) . chr(161) => 's',
                chr(197) . chr(162) => 'T', chr(197) . chr(163) => 't',
                chr(197) . chr(164) => 'T', chr(197) . chr(165) => 't',
                chr(197) . chr(166) => 'T', chr(197) . chr(167) => 't',
                chr(197) . chr(168) => 'U', chr(197) . chr(169) => 'u',
                chr(197) . chr(170) => 'U', chr(197) . chr(171) => 'u',
                chr(197) . chr(172) => 'U', chr(197) . chr(173) => 'u',
                chr(197) . chr(174) => 'U', chr(197) . chr(175) => 'u',
                chr(197) . chr(176) => 'U', chr(197) . chr(177) => 'u',
                chr(197) . chr(178) => 'U', chr(197) . chr(179) => 'u',
                chr(197) . chr(180) => 'W', chr(197) . chr(181) => 'w',
                chr(197) . chr(182) => 'Y', chr(197) . chr(183) => 'y',
                chr(197) . chr(184) => 'Y', chr(197) . chr(185) => 'Z',
                chr(197) . chr(186) => 'z', chr(197) . chr(187) => 'Z',
                chr(197) . chr(188) => 'z', chr(197) . chr(189) => 'Z',
                chr(197) . chr(190) => 'z', chr(197) . chr(191) => 's',
                // Decompositions for Latin Extended-B
                chr(200) . chr(152) => 'S', chr(200) . chr(153) => 's',
                chr(200) . chr(154) => 'T', chr(200) . chr(155) => 't',
                // Euro Sign
                chr(226) . chr(130) . chr(172) => 'E',
                // GBP (Pound) Sign
                chr(194) . chr(163) => '',
                // Vowels with diacritic (Vietnamese)
                // unmarked
                chr(198) . chr(160) => 'O', chr(198) . chr(161) => 'o',
                chr(198) . chr(175) => 'U', chr(198) . chr(176) => 'u',
                // grave accent
                chr(225) . chr(186) . chr(166) => 'A', chr(225) . chr(186) . chr(167) => 'a',
                chr(225) . chr(186) . chr(176) => 'A', chr(225) . chr(186) . chr(177) => 'a',
                chr(225) . chr(187) . chr(128) => 'E', chr(225) . chr(187) . chr(129) => 'e',
                chr(225) . chr(187) . chr(146) => 'O', chr(225) . chr(187) . chr(147) => 'o',
                chr(225) . chr(187) . chr(156) => 'O', chr(225) . chr(187) . chr(157) => 'o',
                chr(225) . chr(187) . chr(170) => 'U', chr(225) . chr(187) . chr(171) => 'u',
                chr(225) . chr(187) . chr(178) => 'Y', chr(225) . chr(187) . chr(179) => 'y',
                // hook
                chr(225) . chr(186) . chr(162) => 'A', chr(225) . chr(186) . chr(163) => 'a',
                chr(225) . chr(186) . chr(168) => 'A', chr(225) . chr(186) . chr(169) => 'a',
                chr(225) . chr(186) . chr(178) => 'A', chr(225) . chr(186) . chr(179) => 'a',
                chr(225) . chr(186) . chr(186) => 'E', chr(225) . chr(186) . chr(187) => 'e',
                chr(225) . chr(187) . chr(130) => 'E', chr(225) . chr(187) . chr(131) => 'e',
                chr(225) . chr(187) . chr(136) => 'I', chr(225) . chr(187) . chr(137) => 'i',
                chr(225) . chr(187) . chr(142) => 'O', chr(225) . chr(187) . chr(143) => 'o',
                chr(225) . chr(187) . chr(148) => 'O', chr(225) . chr(187) . chr(149) => 'o',
                chr(225) . chr(187) . chr(158) => 'O', chr(225) . chr(187) . chr(159) => 'o',
                chr(225) . chr(187) . chr(166) => 'U', chr(225) . chr(187) . chr(167) => 'u',
                chr(225) . chr(187) . chr(172) => 'U', chr(225) . chr(187) . chr(173) => 'u',
                chr(225) . chr(187) . chr(182) => 'Y', chr(225) . chr(187) . chr(183) => 'y',
                // tilde
                chr(225) . chr(186) . chr(170) => 'A', chr(225) . chr(186) . chr(171) => 'a',
                chr(225) . chr(186) . chr(180) => 'A', chr(225) . chr(186) . chr(181) => 'a',
                chr(225) . chr(186) . chr(188) => 'E', chr(225) . chr(186) . chr(189) => 'e',
                chr(225) . chr(187) . chr(132) => 'E', chr(225) . chr(187) . chr(133) => 'e',
                chr(225) . chr(187) . chr(150) => 'O', chr(225) . chr(187) . chr(151) => 'o',
                chr(225) . chr(187) . chr(160) => 'O', chr(225) . chr(187) . chr(161) => 'o',
                chr(225) . chr(187) . chr(174) => 'U', chr(225) . chr(187) . chr(175) => 'u',
                chr(225) . chr(187) . chr(184) => 'Y', chr(225) . chr(187) . chr(185) => 'y',
                // acute accent
                chr(225) . chr(186) . chr(164) => 'A', chr(225) . chr(186) . chr(165) => 'a',
                chr(225) . chr(186) . chr(174) => 'A', chr(225) . chr(186) . chr(175) => 'a',
                chr(225) . chr(186) . chr(190) => 'E', chr(225) . chr(186) . chr(191) => 'e',
                chr(225) . chr(187) . chr(144) => 'O', chr(225) . chr(187) . chr(145) => 'o',
                chr(225) . chr(187) . chr(154) => 'O', chr(225) . chr(187) . chr(155) => 'o',
                chr(225) . chr(187) . chr(168) => 'U', chr(225) . chr(187) . chr(169) => 'u',
                // dot below
                chr(225) . chr(186) . chr(160) => 'A', chr(225) . chr(186) . chr(161) => 'a',
                chr(225) . chr(186) . chr(172) => 'A', chr(225) . chr(186) . chr(173) => 'a',
                chr(225) . chr(186) . chr(182) => 'A', chr(225) . chr(186) . chr(183) => 'a',
                chr(225) . chr(186) . chr(184) => 'E', chr(225) . chr(186) . chr(185) => 'e',
                chr(225) . chr(187) . chr(134) => 'E', chr(225) . chr(187) . chr(135) => 'e',
                chr(225) . chr(187) . chr(138) => 'I', chr(225) . chr(187) . chr(139) => 'i',
                chr(225) . chr(187) . chr(140) => 'O', chr(225) . chr(187) . chr(141) => 'o',
                chr(225) . chr(187) . chr(152) => 'O', chr(225) . chr(187) . chr(153) => 'o',
                chr(225) . chr(187) . chr(162) => 'O', chr(225) . chr(187) . chr(163) => 'o',
                chr(225) . chr(187) . chr(164) => 'U', chr(225) . chr(187) . chr(165) => 'u',
                chr(225) . chr(187) . chr(176) => 'U', chr(225) . chr(187) . chr(177) => 'u',
                chr(225) . chr(187) . chr(180) => 'Y', chr(225) . chr(187) . chr(181) => 'y',
                // Vowels with diacritic (Chinese, Hanyu Pinyin)
                chr(201) . chr(145) => 'a',
                // macron
                chr(199) . chr(149) => 'U', chr(199) . chr(150) => 'u',
                // acute accent
                chr(199) . chr(151) => 'U', chr(199) . chr(152) => 'u',
                // caron
                chr(199) . chr(141) => 'A', chr(199) . chr(142) => 'a',
                chr(199) . chr(143) => 'I', chr(199) . chr(144) => 'i',
                chr(199) . chr(145) => 'O', chr(199) . chr(146) => 'o',
                chr(199) . chr(147) => 'U', chr(199) . chr(148) => 'u',
                chr(199) . chr(153) => 'U', chr(199) . chr(154) => 'u',
                // grave accent
                chr(199) . chr(155) => 'U', chr(199) . chr(156) => 'u',
            );

            $string = strtr($string, $chars);
        } else {
            $chars = array();
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128) . chr(131) . chr(138) . chr(142) . chr(154) . chr(158)
                . chr(159) . chr(162) . chr(165) . chr(181) . chr(192) . chr(193) . chr(194)
                . chr(195) . chr(196) . chr(197) . chr(199) . chr(200) . chr(201) . chr(202)
                . chr(203) . chr(204) . chr(205) . chr(206) . chr(207) . chr(209) . chr(210)
                . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . chr(217) . chr(218)
                . chr(219) . chr(220) . chr(221) . chr(224) . chr(225) . chr(226) . chr(227)
                . chr(228) . chr(229) . chr(231) . chr(232) . chr(233) . chr(234) . chr(235)
                . chr(236) . chr(237) . chr(238) . chr(239) . chr(241) . chr(242) . chr(243)
                . chr(244) . chr(245) . chr(246) . chr(248) . chr(249) . chr(250) . chr(251)
                . chr(252) . chr(253) . chr(255);

            $chars['out'] = 'EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy';

            $string = strtr($string, $chars['in'], $chars['out']);
            $double_chars = array();
            $double_chars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $double_chars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($double_chars['in'], $double_chars['out'], $string);
        }

        return $string;
    }

    /**
     * Sets or resets the binary-safe encoding for mbstring functions.
     *
     * @param bool $reset Indicates whether to reset the encoding. Default is false.
     *
     * @return void
     */
    private static function __mbstring_binary_safe_encoding($reset = false)
    {
        static $encodings = [];
        static $overloaded = null;

        if (is_null($overloaded))
            $overloaded = function_exists('mb_internal_encoding') && (ini_get('mbstring.func_overload') & 2);

        if (false === $overloaded) {
            return;
        }

        if (!$reset) {
            $encoding = mb_internal_encoding();
            array_push($encodings, $encoding);
            mb_internal_encoding('UTF-8');
        }

        if ($reset && $encodings) {
            $encoding = array_pop($encodings);
            mb_internal_encoding($encoding);
        }
    }

    /**
     * Determines if a given string appears to be UTF-8 encoded.
     *
     * @param string $str The string to check.
     *
     * @return bool True if the string appears to be UTF-8 encoded, false otherwise.
     */
    private static function __seems_utf8($str)
    {
        self::__mbstring_binary_safe_encoding();
        $length = strlen($str);
        self::__reset_mbstring_encoding();
        for ($i = 0; $i < $length; $i++) {
            $c = ord($str[$i]);
            if ($c < 0x80) $n = 0; // 0bbbbbbb
            elseif (($c & 0xE0) == 0xC0) $n = 1; // 110bbbbb
            elseif (($c & 0xF0) == 0xE0) $n = 2; // 1110bbbb
            elseif (($c & 0xF8) == 0xF0) $n = 3; // 11110bbb
            elseif (($c & 0xFC) == 0xF8) $n = 4; // 111110bb
            elseif (($c & 0xFE) == 0xFC) $n = 5; // 1111110b
            else return false; // Does not match any model
            for ($j = 0; $j < $n; $j++) { // n bytes matching 10bbbbbb follow ?
                if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                    return false;
            }
        }
        return true;
    }

    /**
     * Resets the mbstring encoding to its default value.
     *
     * @return void
     */
    private static function __reset_mbstring_encoding()
    {
        self::__mbstring_binary_safe_encoding(true);
    }
}