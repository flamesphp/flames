<?php

namespace Flames\Orm\Database\QueryBuilder;

use Flames\Collection\Arr;
use Flames\Environment;
use Flames\Orm\Database\Driver\MariaDb;
use Flames\Orm\Database\Driver\MySql;
use PDO;
use Exception;

/**
 * @internal
 */
class DefaultEx
{
    public function __construct($connection) {}

    public function setTable($table) {}

    public function setModel($model) {}
}
