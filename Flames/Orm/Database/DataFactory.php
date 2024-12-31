<?php

namespace Flames\Orm\Database;

use Flames\Environment;

/**
 * @internal
 */
class DataFactory
{
    protected static $databases = [];

    public static function getConfigByDatabase($database = null)
    {
        if ($database === null) {
            $database = Environment::get('DATABASE_DEFAULT');
        }

        if (isset(self::$databases[$database]) === true) {
            return self::$databases[$database];
        }


        $databaseUpper = strtoupper($database);

        $data = Arr();
        $data->type = (Environment::get('DATABASE_' . $databaseUpper . '_DRIVER'));
        $data->name = (Environment::get('DATABASE_' . $databaseUpper . '_NAME'));
        $data->host = (Environment::get('DATABASE_' . $databaseUpper . '_HOST'));
        $data->port = (Environment::get('DATABASE_' . $databaseUpper . '_PORT'));
        $data->user = (Environment::get('DATABASE_' . $databaseUpper . '_USER'));
        $data->password = (Environment::get('DATABASE_' . $databaseUpper . '_PASSWORD'));

        self::$databases[$database] = $data;
        return self::$databases[$database];
    }
}