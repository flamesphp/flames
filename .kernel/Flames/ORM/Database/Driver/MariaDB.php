<?php

namespace Flames\ORM\Database\Driver;

use Flames\Collection\Arr;
use Flames\ORM\Database\Driver;

class MariaDB extends Driver
{
    public function find(int $id)
    {


    }

    public function getWithFilters(Arr|array $filters)
    {

    }

    public function insert(int $id, Arr|array $data)
    {

    }

    public function update(int $id, Arr|array $data)
    {

    }

    protected const __VERSION__ = 1;

    protected function __checkStructure() : bool
    {
        if (parent::__checkStructure() === true) {
            return true;
        }

        $path = (ROOT_PATH . str_replace('\\', '/', $this->data->class) . '.php');
        $hash = sha1(filemtime($path));

        // Get migration table
        try {
            $query = $this->connection->query('SELECT * FROM `flames_migration`;');
        } catch (\PDOException $_) {
            // Case fail, create migration table
            $this->__mountMigration();
            $query = $this->connection->query('SELECT * FROM `flames_migration`;');
        }

        $rows = $query->fetchAll();
        // Case migration empty, mount table
        if (count($rows) === 0) {
            $this->__mountTable($hash);
            $query = $this->connection->query('SELECT * FROM `flames_migration`;');
            $rows = $query->fetchAll();
        }

        // Case migration not empty, but table not implement yet, mount table
        $migration = null;
        foreach ($rows as $row) {
            if ($row['class'] === $this->data->class) {
                $migration = $row;
                break;
            }
        }
        if ($migration === null) {
            $this->__mountTable($hash);
            $query = $this->connection->query('SELECT * FROM `flames_migration`;');
            $rows = $query->fetchAll();
        }

        // Update static hashs (cache migration metadata for other models)
        foreach ($rows as $row) {
            if ($row['version'] !== self::__VERSION__) {
                continue;
            }

            self::$tablesMigrations[$row['class']] = $row['hash'];
        }

        // Verify migration hash (is updated?)
        if (isset(self::$tablesMigrations[$this->data->class]) === true && self::$tablesMigrations[$this->data->class] === $hash) {
            self::$tableUpdateds[] = $this->data->class;
            return true;
        }

        // Update migration
        $this->__updateTable($hash);
        self::$tablesMigrations[$this->data->class] = $hash;
        self::$tableUpdateds[] = $this->data->class;
        return true;
    }

    protected function __mountMigration()
    {
        $query = <<<SQL
            START TRANSACTION;
                CREATE TABLE `flames_migration` (
                    `id` bigint(20) NOT NULL,
                    `class` varchar(1024) NOT NULL,
                    `hash` varchar(40) NOT NULL,
                    `version` int(11) NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

                ALTER TABLE `flames_migration`
                    ADD PRIMARY KEY (`id`);

                ALTER TABLE `flames_migration`
                    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
            COMMIT;
SQL;
        $this->connection->query($query);
    }

    protected function __mountTable(string $hash)
    {
        $query = <<<SQL
            START TRANSACTION;
                CREATE TABLE `{$this->data->table}` (
SQL;
        $query .= "\n\t\t\t";

        $columns = [];
        foreach ($this->data->column as $column) {
            $column = self::__validateColumn($column);
            $query .= ('`' . $column->name . '` ');
            $column->base = self::__createColumnBase($column);
            $query .= $column->base;
            $query .= ",\n\t\t\t";
            $columns[] = $column;
        }
        $query = (substr($query, 0, strlen($query) - 5) . "\n\t\t) ENGINE=InnoDB DEFAULT CHARSET=latin1;\n");

        foreach ($columns as $column) {
            if ($column->primary === true) {
                $query .= ("\n\t\tALTER TABLE `" . $this->data->table . '` ADD PRIMARY KEY (`' . $column->name . '`);');
            }
            if ($column->index === true) {
                $query .= ("\n\t\tALTER TABLE `" . $this->data->table . '` ADD INDEX (`' . $column->name . '`);');
            }
            if ($column->unique === true) {
                $query .= ("\n\t\tALTER TABLE `" . $this->data->table . '` ADD UNIQUE (`' . $column->name . '`);');
            }

            if ($column->autoIncrement === true) {
                $query .= ("\n\t\tALTER TABLE `" . $this->data->table . '` MODIFY `' . $column->name . '` ' . $column->base . ' AUTO_INCREMENT;');
            }
        }

        $query .= ("\n\n\t\t" . 'INSERT INTO `flames_migration` (`id`, `class`, `hash`, `version`) VALUES (NULL, \'' . str_replace('\\', '\\\\', $this->data->class) .'\', \'' . $hash . '\', ' . self::__VERSION__ . '); ' . "\n\tCOMMIT;");
        $this->connection->query($query);
    }

    protected function __updateTable(string $hash)
    {
        $query = $this->connection->query('SHOW COLUMNS FROM ' . $this->data->table . ';');
        $dbColumns = $query->fetchAll();

        $query = "START TRANSACTION;";

        $columns   = [];
        $missingDb = [];

        // Update columns types
        foreach ($this->data->column as $column) {
            $column = self::__validateColumn($column);
            $columns[] = $column;

            $dbColumn = null;
            foreach ($dbColumns as $_dbColumn) {
                if ($_dbColumn['Field'] === $column->name) {
                    $dbColumn = $_dbColumn;
                    break;
                }
            }

            if ($dbColumn === null) {
                $missingDb[] = $column;
                continue;
            }

            // TODO: run only if really changes column (func: __createColumnDbBase)
            $preQuery = ("\n\tALTER TABLE `" . $this->data->table . '` MODIFY `' . $column->name . '` ' . $this->__createColumnBase($column));
            if ($column->autoIncrement === true) {
                $preQuery .= ' AUTO_INCREMENT';
            }
            $preQuery .= ';';

            $query .= ($preQuery);
        }

        // Add new columns in correct order
        if (count($missingDb) > 0) {
            foreach ($missingDb as $column) {
                $preQuery = ("\n\tALTER TABLE `" . $this->data->table . '` ADD `' . $column->name . '` ' . $this->__createColumnBase($column));

                // Get one column before new
                $lastColumn = null;
                foreach ($columns as $afterColumn) {
                    if ($lastColumn === null) {
                        $lastColumn = $afterColumn;
                        continue;
                    }
                    if ($afterColumn->name === $column->name) {
                        break;
                    }
                    $lastColumn = $afterColumn;
                }

                if ($lastColumn !== null) {
                    $preQuery .= (' AFTER `' . $lastColumn->name . '`');
                }

                $preQuery .= ';';
                $query .= $preQuery;

                // Add new columns auto increment
                if ($column->autoIncrement === true) {
                    $query .= ("\n\tALTER TABLE `" . $this->data->table . '` MODIFY `' . $column->name . '` ' . $column->base . ' AUTO_INCREMENT;');
                }
            }
        }

        // Update primary key/index
        $_query = $this->connection->query('SHOW INDEX FROM ' . $this->data->table . ';');
        $dbIndexes = $_query->fetchAll();

        foreach ($columns as $column) {
            $dbColumn = null;
            foreach ($dbColumns as $_dbColumn) {
                if ($_dbColumn['Field'] === $column->name) {
                    $dbColumn = $_dbColumn;
                    break;
                }
            }

            // Case primary key, just ignore, query wont change
            if ($column->primary === true && ($dbColumn === null || $dbColumn['Key'] !== 'PRI')) {
                $query .= ("\n\tALTER TABLE `" . $this->data->table . '` ADD PRIMARY KEY (`' . $column->name . '`);');
            }

            // Case index, verify if already exists index or unique
            if ($column->index === true) {
                $found = false;
                foreach ($dbIndexes as $dbIndex) {
                    if ($dbIndex['Key_name'] === 'PRIMARY') {
                        continue;
                    }
                    $found = true;
                    break;
                }

                if ($found === false) {
                    $query .= ("\n\tALTER TABLE `" . $this->data->table . '` ADD INDEX (`' . $column->name . '`);');
                }
            }

            // Case index, verify if already exists unique (if exists index, will be duplicated)
            // TODO: drop index on change to unique
            if ($column->unique === true) {
                $found = false;
                foreach ($dbIndexes as $dbIndex) {
                    if ($dbIndex['Key_name'] === 'PRIMARY' || $dbIndex['Non_unique'] === 1) {
                        continue;
                    }
                    $found = true;
                    break;
                }

                if ($found === false) {
                    $query .= ("\n\tALTER TABLE `" . $this->data->table . '` ADD UNIQUE (`' . $column->name . '`);');
                }
            }
        }

        $query .= "\nCOMMIT;";
        $this->connection->query($query);

        // Verify column order
        // TODO: costs too much processing giant data, already adding new columns in correct order, may not do
//        $first = true;
//        $last = null;
//        foreach ($columns as $column) {
//            if ($first === true) {
//                $last = $column;
//                $first = false;
//                continue;
//            }
//
//            $preQuery = ("\n\tALTER TABLE `" . $this->data->table . '` CHANGE `' . $column->name . '` `' . $column->name . '` ' . $this->__createColumnBase($column));
//            if ($column->autoIncrement === true) {
//                $preQuery .= ' AUTO_INCREMENT';
//            }
//            $preQuery .= ' AFTER `' . $last->name . '`;';
//            $query .= $preQuery;
//        }

        // Drop removed columns
        $query = $this->connection->query('SHOW COLUMNS FROM ' . $this->data->table . ';');
        $dbColumns = $query->fetchAll();

        $extraColumns = [];
        foreach ($dbColumns as $dbColumn) {
            $extraColumns[$dbColumn['Field']] = $dbColumn;
        }

        foreach ($columns as $column) {
            foreach ($dbColumns as $dbColumn) {
                if ($dbColumn['Field'] === $column->name) {
                    unset($extraColumns[$column->name]);
                    break;
                }
            }
        }

        if (count($extraColumns) > 0) {
            $query = "START TRANSACTION;";

            foreach ($extraColumns as $extraColumn) {
                $query .= ("\n\tALTER TABLE `" . $this->data->table . '` DROP COLUMN `' . $extraColumn['Field'] . '`;');
            }

            $query .= ("\n\t" . 'UPDATE `flames_migration` SET `hash` = \'' . $hash . '\', `version` = \'' . self::__VERSION__ . '\' WHERE `flames_migration`.`class` = \'' . str_replace('\\', '\\\\', $this->data->class) . '\'; ' . "\n\tCOMMIT;");
            $this->connection->query($query);
        } else {
            $this->connection->query('UPDATE `flames_migration` SET `hash` = \'' . $hash . '\', `version` = \'' . self::__VERSION__ . '\' WHERE `flames_migration`.`class` = \'' . str_replace('\\', '\\\\', $this->data->class) . '\';');
        }
    }

    protected function __createColumnBase(Arr $column) : string
    {
        $query = $column->type;

        if ($column->type === 'bigint' || $column->type === 'int' || $column->type === 'varchar') {
            $query .= ('(' . $column->size . ')');
        }
        if ($column->nullable === false) {
            $query .= ' NOT NULL';
        } else {
            if ($column->default === null) {
                $query .= ' DEFAULT NULL';
            } else {
                $query .= (' DEFAULT \'' . $column->default . '\'');
            }
        }

        return $query;
    }

//    protected function __createColumnDbBase(array $column) : string
//    {
//        $query = $column['Type'];
//
//        if ($column['Null'] === 'NO') {
//            $query .= ' NOT NULL';
//        } else {
//            if ($column['Default'] === null) {
//                $query .= ' DEFAULT NULL';
//            }
//            else {
//                $query .= (' DEFAULT \'' . $column['Default'] . '\'');
//            }
//        }
//
//        return $query;
//    }

    protected function __validateColumn(Arr $column) : Arr
    {
        if ($column->type === 'string') {
            $column->type = 'varchar';
        }

        if ($column->type === 'bigint') {
            if ($column->size === null) {
                $column->size = 20;
            }
        }
        elseif ($column->type === 'varchar') {
            if ($column->size === null) {
                $column->size = 256;
            }
        }
        elseif ($column->type === 'int') {
            if ($column->size === null) {
                $column->size = 11;
            }
        }

        return $column;
    }
}
