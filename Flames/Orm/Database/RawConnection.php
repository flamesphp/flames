<?php

namespace Flames\Orm\Database;

use Flames\Collection\Arr;
use Flames\Environment;
use Flames\Orm\Database\Driver\MariaDB;
use Flames\Orm\Database\Driver\MySQL;
use PDO;
use PDOException;

/**
 * @internal
 */
class RawConnection
{
    protected static $connections = [];

    public static function getByConfigAndDatabase($config, string $database = null)
    {
        if ($database === null) {
            $database = sha1($config);
        }

        if (isset(self::$connections[$database]) === true) {
            return self::$connections[$database];
        }

        // PDO Connection
        if ($config->type === 'mariadb' || $config->type === 'mysql') {
            try {
                $connectionUri = ('mysql:host='. $config->host . ';dbname=' . $config->name . ';port=' . $config->port . ';charset=utf8mb4');
                $connection = new PDO($connectionUri, $config->user, $config->password);
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                self::$connections[$database] = $connection;
            } catch(PDOException $e) {
                throw new \Error($e->getMessage());
            }
        }

        return self::$connections[$database];
    }
}
