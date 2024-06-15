<?php

namespace Flames\Orm\Database;

use Flames\Collection\Arr;
use Flames\Environment;
use Flames\ORM\Database\Driver\MariaDB;
use Flames\ORM\Database\Driver\MySQL;
use PDO;
use PDOException;

class RawConnection
{
    protected PDO|null $connection = null;
    protected Driver|MySQL|MariaDB|null $driver = null;
    protected string|null $driverType = null;

    public function __construct(string|null $database = null)
    {
        if ($database === null) {
            $database = Environment::get('DATABASE_DEFAULT');
        }

        $database = strtoupper($database);
        $this->driverType = (Environment::get('DATABASE_' . $database . '_DRIVER'));

        if ($this->driverType === 'mysql') {
            $this->createMysql($database);
            return;
        } elseif ($this->driverType === 'mariadb') {
            $this->createMariaDb($database);
            return;
        }

        throw new \Exception('Unknown driver type ' . $this->driverType . '.');
    }

    protected function createMariaDb(string $database): void
    {
        $name     = (Environment::get('DATABASE_' . $database . '_NAME'));
        $host     = (Environment::get('DATABASE_' . $database . '_HOST'));
        $port     = (Environment::get('DATABASE_' . $database . '_PORT'));
        $user     = (Environment::get('DATABASE_' . $database . '_USER'));
        $password = (Environment::get('DATABASE_' . $database . '_PASSWORD'));

        try {
            $connectionUri = ('mysql:host='. $host . ';dbname=' . $name . ';port=' . $port);
            $connection = new PDO($connectionUri, $user, $password);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connection = $connection;
        } catch(PDOException $e) {
            throw new \Error($e->getMessage());
        }
    }

    protected function createMysql(string $database): void
    {
        $this->createMariaDb($database);
    }

    public function getConnection() : PDO|null
    {
        return $this->connection;
    }

    public function getDriver(Arr $data) : Driver
    {
        if ($this->driver === null) {
            if ($this->driverType === 'mysql') {
                $this->driver = new MySQL($this->connection, $data);
            }
            elseif ($this->driverType === 'mariadb') {
                $this->driver = new MariaDB($this->connection, $data);
            }
        }

        return $this->driver;
    }
}
