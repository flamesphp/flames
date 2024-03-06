<?php

namespace Flames\ORM\Database;

use Flames\Collection\Arr;
use PDO;

abstract class Driver
{
    protected static array|null $tableUpdateds = null;
    protected static array|null $tablesMigrations = null;

    protected string $driver;
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

    public function find(int $id)
    {
        return null;
    }

    public function getWithFilters(Arr|array $filters)
    {
        return null;
    }

    public function insert(int $id, Arr|array $data)
    {
        return null;
    }

    public function update(int $id, Arr|array $data)
    {
        return null;
    }
}
