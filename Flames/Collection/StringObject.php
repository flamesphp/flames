<?php

namespace Flames\Collection
{

    /**
     * Description for the class
     * @property int $length
     * @property int $count
     */
    final class StringObject// implements \ArrayAccess, \Iterator
    {
        // TODO: everything
//        const string PAD_LEFT = 'STR_PAD_LEFT';
//        const string PAD_RIGHT = 'STR_PAD_RIGHT';
//        const string PAD_BOTH = 'STR_PAD_BOTH';
//
//        const string TRUNCATE_BEFORE = 'BEFORE';
//        const string TRUNCATE_AFTER = 'AFTER';
//
//        const string DIRECTION_LEFT_TO_RIGHT = '>';
//        const string DIRECTION_RIGHT_TO_LEFT = '<';

        private string $value = '';

//        private $charArr = null;
//
//        private $iteratorPosition = 0;
//        private static $implements = null;

        public function __construct(mixed $value = null)
        {
            $this->value = (string)$value;
        }

        public function __get($name)
        {
            $name = strtolower($name);

            if ($name == 'length' || $name == 'count') {
                return self::count($this->value);
            }

            return null;
        }

        public function length(bool $multibyte = false) : int
        {
            return Strings::length($this->value, $multibyte);
        }

        public function count(bool $multibyte = false) : int
        {
            return Strings::count($this->value, $multibyte);
        }

        public function toLower( bool $multibyte = false) : StringObject
        {
            $this->value = Strings::toLower($this->value, $multibyte);
            return $this;
        }

        public function toUpper(bool $multibyte = false) : StringObject
        {
            $this->value = Strings::toUpper($this->value, $multibyte);
            return $this;
        }

        public function startsWith(mixed $needle, bool $caseSensitive = true) : bool
        {
            return Strings::startsWith($this->value, $needle, $caseSensitive);
        }

        public function endsWith(mixed $needle, bool $caseSensitive = true) : bool
        {
            return Strings::endsWith($this->value, $needle, $caseSensitive);
        }

        public function contains(mixed $needle, bool $caseSensitive = true) : bool
        {
            return Strings::contains($this->value, $needle, $caseSensitive);
        }

        public function containsAny(mixed $array, bool $caseSensitive = true) : bool
        {
            return Strings::containsAny($this->value, $array, $caseSensitive);
        }

        public function equals(mixed $needle, bool $caseSensitive = true) : bool
        {
            return Strings::equals($this->value, $needle, $caseSensitive);
        }

        public function equalsAny(mixed $array, bool $caseSensitive = true) : bool
        {
            return Strings::equalsAny($this->value, $array, $caseSensitive);
        }

        public  function isEmpty(mixed $value) : bool
        {
            return empty($this->value);
        }

        public function replace(mixed $needle, mixed $replace) : StringObject
        {
            $this->value = Strings::replace($this->value, $needle, $replace);
            return $this;
        }

        public function remove(mixed $needle) : StringObject
        {
            $this->value = Strings::remove($this->value, $needle);
            return $this;
        }

        public function encode(bool $raw = false) : StringObject
        {
            $this->value = Strings::encode($this->value, $raw);
            return $this;
        }

        public function decode(bool $raw = false) : StringObject
        {
            $this->value = Strings::decode($this->value, $raw);
            return $this;
        }

        public function split(string $needle = ',', bool $clearEmpty = true, bool $keepDelimiter = false) : Arr
        {
            return Strings::split($this->value, $needle, $clearEmpty, $keepDelimiter);
        }

        public function splitLength(mixed $length) : Arr
        {
            return Strings::splitLength($this->value, $length);
        }

        public function splitWords() : Arr
        {
            return Strings::splitWords($this->value);
        }


        public function splitLines(): Arr
        {
            return Strings::splitLines($this->value);
        }

        public function sub(mixed $start, mixed $length = null) : StringObject
        {
            $this->value = Strings::sub($this->value, $start, $length);
            return $this;
        }

        public function indexOf(mixed $needle, bool $caseSensitive = true) : int|null
        {
            return Strings::indexOf($this->value, $needle, $caseSensitive);
        }

        public function lastIndexOf(mixed $needle, bool $caseSensitive = true) : int|null
        {
            return Strings::lastIndexOf($this->value, $needle, $caseSensitive);
        }

        public function trim(mixed $charList, bool $multibyte = false) : StringObject
        {
            $this->value = Strings::trim($this->value, $charList, $multibyte);
            return $this;
        }

        public function addSlashes() : StringObject
        {
            $this->value = Strings::addSlashes($this->value);
            return $this;
        }

        public function removeSlashes() : StringObject
        {
            $this->value = Strings::removeSlashes($this->value);
            return $this;
        }

        public function toBase64() : StringObject
        {
            $this->value = Strings::toBase64($this->value);
            return $this;
        }

        public static function fromBase64(mixed $value) : StringObject|null
        {
            $value = Strings::fromBase64($value);
            return new StringObject($value);
        }

        public function getOnlyNumbers(mixed $whiteList = '') : StringObject
        {
            $this->value = Strings::getOnlyNumbers($this->value, $whiteList);
            return $this;
        }

        public function getOnlyLetters() : StringObject
        {
            $this->value = Strings::getOnlyLetters($this->value);
            return $this;
        }

        public function limit(mixed $limit = 10, $returnString = true) : StringObject
        {
            $this->value = Strings::limit($this->value, $limit, $returnString);
            return $this;
        }

        // TODO: to UTF-8
        // function toUTF8

        public function removeAccents() : StringObject
        {
            $this->value = Strings::removeAccents($this->value);
            return $this;
        }

//
//
//        protected function updateCharArr()
//        {
//            if ($this->lastCharArrString != $this->value) {
//                $this->lastCharArrString = $this->value;
//                $this->charArr = $this->toCharArr(true);
//            }
//        }

//        // ITERATOR
//        #[\ReturnTypeWillChange]
//        function rewind()
//        {
//            $this->iteratorPosition = 0;
//        }
//
//        #[\ReturnTypeWillChange]
//        function current()
//        {
//            $this->updateCharArr();
//            if (isset($this->charArr[$this->iteratorPosition]))
//                return $this->charArr[$this->iteratorPosition];
//            return null;
//        }
//
//        #[\ReturnTypeWillChange]
//        function key()
//        {
//            return $this->iteratorPosition;
//        }
//
//        #[\ReturnTypeWillChange]
//        function next()
//        {
//            ++$this->iteratorPosition;
//        }
//
//        #[\ReturnTypeWillChange]
//        function valid()
//        {
//            $this->updateCharArr();
//            return isset($this->charArr[$this->iteratorPosition]);
//        }
//        // END ITERATOR
//
//        // ARRAYACCESS
//        #[\ReturnTypeWillChange]
//        public function offsetExists($offset)
//        {
//            $this->updateCharArr();
//            return (isset($this->charArr[$offset]));
//        }
//
//        #[\ReturnTypeWillChange]
//        public function offsetGet($offset)
//        {
//            $this->updateCharArr();
//            if (isset($this->charArr[$offset]))
//                return $this->charArr[$offset];
//            return null;
//        }
//
//        #[\ReturnTypeWillChange]
//        public function offsetSet($offset, $value)
//        {
//            $this->updateCharArr();
//            $value = Strings($value)->toString();
//
//            $this->charArr[$offset] = $value;
//            $this->value = \Strings::fromCharArr($this->charArr)->toString();
//
//            $this->lastCharArrString = null;
//        }
//
//        #[\ReturnTypeWillChange]
//        public function offsetUnset($offset)
//        {
//            $this->updateCharArr();
//
//            if (isset($this->charArr[$offset])) {
//                unset($this->charArr[$offset]);
//                $this->value = \Strings::fromCharArr($this->charArr)->toString();
//                $this->lastCharArrString = null;
//            }
//        }
        // END ARRAYACCESS

        public function __toString()
        {
            return $this->toString();
        }

        public function toString()
        {
            return $this->value;
        }

        public function toInt()
        {
            return Ints::parse($this->value);
        }

        public function toFloat()
        {
            return Floats::parse($this->value);
        }

        public function toBool()
        {
            return Bools::parse($this->value);
        }
    }
}