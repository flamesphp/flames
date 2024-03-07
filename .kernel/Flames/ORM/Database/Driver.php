<?php

namespace Flames\ORM\Database;

use Flames\Collection\Arr;
use Flames\Model;
use PDO;

abstract class Driver
{
    protected static array|null $tableUpdateds = null;
    protected static array|null $tablesMigrations = null;

    protected PDO $connection;
    protected Arr $data;

    public function __construct(PDO $connection, Arr $data)
    {
        $this->connection = $connection;
        $this->data = $data;
        $this->__checkStructure();
    }

    protected function __checkStructure() : bool
    {
        if (self::$tableUpdateds === null)
            self::$tableUpdateds = [];

        if (in_array($this->data->class, self::$tableUpdateds) === true) {
            return true;
        }

        return false;
    }

    public function getByIndex(string $index) : Model|null
    {
        return null;
    }

    public function getWithFilters(Arr|array $filters) : Arr|null
    {
        return null;
    }

    public function insert(Arr|array $data) : mixed
    {
        return null;
    }

    public function update(mixed $index, Arr|array $data) : mixed
    {
        return null;
    }

    public function cast(string $key, mixed $value = null) : mixed
    {
        if (isset($this->data->column->{$key}) === false) {
            return null;
        }

        $column = $this->data->column->{$key};

        if ($column->type === 'varchar' || $column->type === 'string') {
            if ($value === null) {
                if ($column->nullable === true) {
                    return null;
                }

                return $column->default;
            }

            $value = (string)$value;
            if ($column->size !== null) {
                if (strlen($value) > $column->size) {
                    $value = substr($value, 0, $column->size);
                }
            }

            return $value;
        }

        elseif ($column->type === 'bigint' || $column->type === 'int') {
            if ($value === null) {
                if ($column->nullable === true) {
                    return null;
                }

                if ($column->default === null) {
                    return 0;
                }
                return $column->default;
            }

            return (int)$value;
        }

        elseif ($column->type === 'float') {
            if ($value === null) {
                if ($column->nullable === true) {
                    return null;
                }

                if ($column->default === null) {
                    return (float)0;
                }
                return $column->default;
            }

            return (float)$value;
        }

        elseif ($column->type === 'bool' || $column->type === 'boolean') {
            if ($value === null) {
                if ($column->nullable === true) {
                    return null;
                }

                return $column->default;
            }

            if ($value === 1 || $value === (float)1 || $value === '1') {
                return true;
            }
            elseif ($value === 0 || $value === (float)0 || $value === '0') {
                return false;
            }
            elseif ($value === 'true') {
                return true;
            }
            elseif ($value === 'false') {
                return false;
            }
            elseif ($value === -1 || $value === (float)-1 || $value === '-1') {
                if ($column->nullable === false) {
                    return $column->default;
                }
                return null;
            }

            return (bool)$value;
        }

        return null;
    }
}
