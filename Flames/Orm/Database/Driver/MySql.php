<?php

namespace Flames\Orm\Database\Driver;

use Flames\Collection\Arr;
use PDO;

/**
 * @internal
 */
class Mysql extends DefaultEx
{
    protected const __VERSION__ = 2;

    protected $connection = null;
    protected $tableUpdateds = [];
    protected $tablesMigrations = [];

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function getQueryBuilder($model)
    {
        $metadata = $model::getMetadata();
        return new \Flames\Orm\Database\QueryBuilder\MySql($this->connection);
    }

    public function migrate($data)
    {
        if ($this->tableUpdateds === null)
            $this->tableUpdateds = [];

        if (in_array($data->class, $this->tableUpdateds) === true) {
            return true;
        }

        $path = (ROOT_PATH . str_replace('\\', '/', $data->class) . '.php');
        $hash = sha1(filemtime($path));

        if (count($this->tablesMigrations) === 0) {
            // Get migration table
            try {
                $query = $this->connection->query('SELECT * FROM `flames_migration`;');
            } catch (PDOException $_) {
                // Case fail, create migration table
                $this->__mountMigration($data);
                $query = $this->connection->query('SELECT * FROM `flames_migration`;');
            }

            $rows = $query->fetchAll();

            // Case migration empty, mount table
            $mountTable = false;
            if (count($rows) === 0) {
                $this->__mountTable($data, $hash);
                $query = $this->connection->query('SELECT * FROM `flames_migration`;');
                $rows = $query->fetchAll();
            }

            // Case migration not empty, but table not implement yet, mount table
            $migration = null;
            foreach ($rows as $row) {
                if ($row['class'] === $data->class) {
                    $migration = $row;
                    break;
                }
            }

            if ($migration === null) {
                $this->__mountTable($data, $hash);
                $this->connection->query('INSERT INTO `flames_migration` (`id`, `class`, `hash`, `version`) VALUES (NULL, \'' . str_replace('\\', '\\\\', $data->class) .'\', \'' . $hash . '\', ' . self::__VERSION__ . '); ' . ';');
                $query = $this->connection->query('SELECT * FROM `flames_migration`;');
                $rows = $query->fetchAll();
            }

            // Update static hashs (cache migration metadata for other models)
            foreach ($rows as $row) {
                if ($row['version'] !== self::__VERSION__) {
                    continue;
                }

                $this->tablesMigrations[$row['class']] = $row['hash'];
            }
        }

        // Verify migration hash (is updated?)
        if (isset($this->tablesMigrations[$data->class]) === true &&  $this->tablesMigrations[$data->class] === $hash) {
            $this->tableUpdateds[] = $data->class;
            return true;
        }


        // Update migration
        $this->__updateTable($data, $hash);
        $this->tablesMigrations[$data->class] = $hash;
        $this->tableUpdateds[] = $data->class;
        return true;
    }

    protected function __mountTable($data, string $hash): void
    {
        $_query =  $this->connection->query('SHOW TABLES;');
        $tables = $_query->fetchAll();

        foreach ($tables as $_table) {
            if ($_table[0] === $data->table) {
                $this->__updateTable($data, $hash);
                return;
            }
        }

        $query = <<<SQL
                CREATE TABLE `{$data->table}` (
SQL;
        $query .= "\n\t\t\t";

        $columns = [];
        foreach ($data->column as $column) {
            $query .= ('`' . $column->name . '` ');
            $column->base = self::__createColumnBase($column);
            $query .= $column->base;
            $query .= ",\n\t\t\t";
            $columns[] = $column;
        }
        $query = (substr($query, 0, strlen($query) - 5) . "\n\t\t) ENGINE=InnoDB DEFAULT CHARACTER  SET=utf8;\n");
        $this->connection->query($query);

        $queries = [];
        foreach ($columns as $column) {
            if ($column->primary === true) {
                $queries[] = ('ALTER TABLE `' . $data->table . '` ADD PRIMARY KEY (`' . $column->name . '`);');
            }
            if ($column->index === true) {
                $queries[] = ('ALTER TABLE `' . $data->table . '` ADD INDEX (`' . $column->name . '`);');
            }
            if ($column->unique === true) {
                $queries[] = ('ALTER TABLE `' . $data->table . '` ADD UNIQUE (`' . $column->name . '`);');
            }

            if ($column->autoIncrement === true) {
                $queries[] = ('ALTER TABLE `' . $data->table . '` MODIFY `' . $column->name . '` ' . $column->base . ' AUTO_INCREMENT;');
            }
        }

        if (count($queries) > 0) {
            foreach ($queries as $query) {
                $this->connection->query($query);
            }
        }

        $this->connection->query('INSERT INTO `flames_migration` (`id`, `class`, `hash`, `version`) VALUES (NULL, \'' . str_replace('\\', '\\\\', $data->class) .'\', \'' . $hash . '\', ' . self::__VERSION__ . '); ' . ';');
    }

    protected function __updateTable($data, string $hash): void
    {
        $query = $this->connection->query('SHOW COLUMNS FROM ' . $data->table . ';');
        $dbColumns = $query->fetchAll();

        $queries = [];

        $columns   = [];
        $missingDb = [];

        // Update columns types
        foreach ($data->column as $column) {
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
            $preQuery = ('ALTER TABLE `' . $data->table . '` MODIFY `' . $column->name . '` ' . $this->__createColumnBase($column));
            if ($column->autoIncrement === true) {
                $preQuery .= ' AUTO_INCREMENT';
            }
            $preQuery .= ';';

            $queries[] = $preQuery;
        }

        // Add new columns in correct order
        if (count($missingDb) > 0) {
            foreach ($missingDb as $column) {
                $preQuery = ('ALTER TABLE `' . $data->table . '` ADD `' . $column->name . '` ' . $this->__createColumnBase($column));

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
                $queries[] = $preQuery;

                // Add new columns auto increment
                if ($column->autoIncrement === true) {
                    $queries[] = ('ALTER TABLE `' . $data->table . '` MODIFY `' . $column->name . '` ' . $column->base . ' AUTO_INCREMENT;');
                }
            }
        }

        // Update primary key/index
        $_query = $this->connection->query('SHOW INDEX FROM ' . $data->table . ';');
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
                $queries[] = ('ALTER TABLE `' . $data->table . '` ADD PRIMARY KEY (`' . $column->name . '`);');
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
                    $queries[] = ('ALTER TABLE `' . $data->table . '` ADD INDEX (`' . $column->name . '`);');
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
                    $queries[] = ('ALTER TABLE `' . $data->table . '` ADD UNIQUE (`' . $column->name . '`);');
                }
            }
        }

        if (count($queries) > 0) {
            foreach ($queries as $query) {
                $this->connection->query($query);
            }
        }

        // Drop removed columns
        $query = $this->connection->query('SHOW COLUMNS FROM ' . $data->table . ';');
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
            $queries = [];
            foreach ($extraColumns as $extraColumn) {
                $queries[] = ('ALTER TABLE `' . $data->table . '` DROP COLUMN `' . $extraColumn['Field'] . '`;');
            }

            $queries[] = ('UPDATE `flames_migration` SET `hash` = \'' . $hash . '\', `version` = \'' . self::__VERSION__ . '\' WHERE `flames_migration`.`class` = \'' . str_replace('\\', '\\\\', $data->class) . '\';');
            foreach ($queries as $query) {
                $this->connection->query($query);
            }
        } else {
            $this->connection->query('UPDATE `flames_migration` SET `hash` = \'' . $hash . '\', `version` = \'' . self::__VERSION__ . '\' WHERE `flames_migration`.`class` = \'' . str_replace('\\', '\\\\', $data->class) . '\';');
        }
    }

    protected function __createColumnBase(Arr $column) : string
    {
        $query = $column->type;

        if ($column->size !== null && ($column->type === 'bigint' || $column->type === 'int' || $column->type === 'varchar' || $column->type === 'tinyint')) {
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
}
