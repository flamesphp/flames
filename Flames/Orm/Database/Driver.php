<?php

namespace Flames\Orm\Database;

use DateTimeZone;
use Exception;
use Flames\Collection\Arr;
use Flames\DateTimeImmutable;
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

    public function getWithFilters(Arr|array $filters, Arr|array $options = null) : Arr
    {
        return Arr();
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
                    return 0.0;
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

            if ($value === 1 || $value === 1.0 || $value === '1') {
                return true;
            }
            elseif ($value === 0 || $value === 0.0 || $value === '0') {
                return false;
            }
            elseif ($value === 'true') {
                return true;
            }
            elseif ($value === 'false') {
                return false;
            }
            elseif ($value === -1 || $value === -1.0 || $value === '-1') {
                if ($column->nullable === false) {
                    return $column->default;
                }
                return null;
            }

            return (bool)$value;
        }

        elseif (strtolower($column->type) === 'datetime') {
            if ($value === null) {
                if ($column->nullable === true) {
                    return null;
                }
                return new \Flames\DateTimeImmutable($column->default);
            }

            if ($value instanceof \Flames\DateTime || $value instanceof \Flames\DateTimeImmutable) {
                return $value;
            }
            if ($value instanceof \DateTimeImmutable) {
                return new \Flames\DateTimeImmutable($value->format('Y-m-d H:i:s.u'), $value->getTimezone());
            }
            if ($value instanceof \DateTime) {
                return new \Flames\DateTimeImmutable($value->format('Y-m-d H:i:s.u'), $value->getTimezone());
            }
            if (is_string($value) === true) {
                return new \Flames\DateTimeImmutable($value);
            }
            if (is_int($value) === true) {
                return (new \Flames\DateTimeImmutable($value))->setTimestamp($value);
            }

            return null;
        }

        return null;
    }

    public function castDb(string $key, mixed $value = null) : mixed
    {
        if (isset($this->data->column->{$key}) === false) {
            return null;
        }

        $column = $this->data->column->{$key};
        if ($column->type === 'datetime') {
            $value = $value->setTimezone(new DateTimeZone('UTC'));
            return $value->format('Y-m-d H:i:s.u');
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    protected function getIndexColumn() : Arr|null
    {
        $indexColumn = null;
        foreach ($this->data->column as $column) {
            if ($column->primary === true || $column->autoIncrement === true) {
                $indexColumn = $column;
                break;
            }
        }
        if ($indexColumn === null) {
            foreach ($this->data->column as $column) {
                if ($column->unique === true) {
                    $indexColumn = $column;
                    break;
                }
            }
        }

        if ($indexColumn === null) {
            throw new Exception('Missing primary or unique column in model ' . $this->data->class . '.');
        }

        return $indexColumn;
    }

    protected function castData(Arr|array $data) : array
    {
        $castData = [];
        foreach ($data as $key => $value) {
            $castData[$key] = self::castDb($key, self::cast($key, $value));
        }
        return $castData;
    }

    /**
     * @throws Exception
     */
    protected function verifyUpdateIndexData(mixed $index, Arr|array $data): void
    {
        if (isset($this->data->column->{$index}) === false) {
            throw new \Exception('Column ' . $index . ' ($model->save() or driver::update()) in class ' . $this->data->class . ' does not exists.');
        }

        if (count($data) === 0) {
            throw new \Exception('Data update payload ($model->save() or driver::update()) in class ' . $this->data->class . ' can\'t be empty.');
        }

        if (isset($data[$index]) === false) {
            throw new \Exception('Data update payload ($model->save() or driver::update()) in class ' . $this->data->class . ' missing where index ' . $index .'.');
        }
    }
}
