<?php

namespace Flames\Collection;

/**
 * Description for the class
 * @property int $length
 * @property int $count
 * @property int $first
 * @property int $last
 */
final class Arr extends \ArrayObject
{
    public function __construct(mixed $value = null)
    {
        if ($value === null || (is_array($value) === false && ($value instanceof Arr) === false)) {
            $value = [];
        }

        parent::__construct($value, \ArrayObject::ARRAY_AS_PROPS);
    }

    public function offsetGet(mixed $key) : mixed
    {
        $key = (string)$key;

        if (($key === 'length' || $key === 'count') && !parent::offsetExists($key))
            return $this->count();
        elseif ($key === 'first' && !parent::offsetExists($key))
            return $this->getFirst();
        elseif ($key === 'last' && !parent::offsetExists($key))
            return $this->getLast();

        return parent::offsetGet($key);
    }

    public function offsetSet(mixed $key, mixed $value) : void
    {
        $key = (string)$key;

        if (empty($key) === true) {
            $lastNumberKey = $this->getLastNumberKey();
            if ($lastNumberKey === null) {
                $key = 0;
            } else {
                $key = ($lastNumberKey + 1);
            }
        }

        parent::offsetSet($key, $value);
    }

    public function length() : int
    {
        return $this->count();
    }

    public function contains(mixed $value) : bool
    {
        foreach ($this as $_ => $_value) {
            if ($value === $_value) {
                return true;
            }
        }

        return false;
    }

    public function containsKey(mixed $key) : bool
    {
        $key = (string)$key;
        return $this->offsetExists($key);
    }

    public function getLastNumberKey() : int|null
    {
        $topKey = 0;
        $keys = $this->getKeys();
        foreach ($keys as $key) {
            $key = (int)$key;
            if ($key > $topKey) {
                $topKey = $key;
            }
        }

        if ($topKey === 0) {
            if ($this->count === 0) {
                return null;
            }
        }

        return $topKey;
    }

    public function add($value, $canDuplicate = true) : Arr
    {
        if ($canDuplicate === false && $this->contains($value)) {
            return $this;
        }

        $this[] = $value;
        return $this;
    }

    public function addKey(mixed $key, mixed $value) : Arr
    {
        $key = (string)$key;
        $this[$key] = $value;
        return $this;
    }

    protected function removeKey(mixed $key) : Arr
    {
        $key = (string)$key;

        if (empty($key) !== true && (isset($this[$key]) || $this->offsetExists($key))) {
            unset($this[$key]);
        }

        return $this;
    }

    public function sort() : Arr
    {
        $this->asort();
        return $this;
    }

    public function sortByKey() : Arr
    {
        $this->ksort();
        return $this;
    }

    public function sortByDelegate(mixed $delegate) : Arr
    {
        $this->uasort(function ($a, $b) use($delegate) {

            $response = $delegate($a, $b);

            if ($response === false) {
                $response = -1;
            }
            elseif ($response === true) {
                $response = 1;
            }
            elseif ($response === null) {
                $response = 0;
            }
            if ($response !== -1 && $response !== 1 && $response !== 0) {
                $response = 0;
            }

            return $response;
        });
        return $this;
    }

    public function toArray(bool $convertChildrens = true)
    {
        $array = $this->getArrayCopy();

        foreach ($array as $key => &$value) {
            if ($value instanceof Arr) {
                $value = $value->toArray();
            }
        }

        return $array;
    }

    public function find(mixed $delegate, bool $isKeyValue = false) : mixed
    {
        if ($isKeyValue === false) {
            foreach ($this as  $value) {
                $isValid = $delegate($value);
                if ($isValid === true) {
                    return $value;
                }
            }
            return null;
        }

        foreach ($this as $key => $value)  {
            $isValid = $delegate($key, $value);
            if ($isValid === true) {
                return Arr(['key' => $key, 'value' => $value]);
            }
        }

        return null;
    }

    public function getKeys() : Arr
    {
        $keys = array_keys((array)$this);
        if ($keys != null) {
            return Arr($keys);
        }
        return Arr();
    }

    public function getLast() : mixed
    {
        if ($this->count <= 0)  {
            return null;
        }
        $keys = $this->getKeys();
        return ($this[($keys[$keys->count - 1])]);
    }

    public function getFirst() : mixed
    {
        if ($this->count <= 0) {
            return null;
        }
        $keys = $this->getKeys();
        return ($this[($keys[0])]);
    }

    public function merge(Arr|array $array = null, $replace = true)
    {
        $thisArray = $this->toArray();
        $array = Arr($array)->toArray();

        $newArray = (($replace === true)
            ? array_replace_recursive($thisArray, $array)
            : array_merge_recursive($thisArray, $array)
        );

        return Arr($newArray);
    }

}