<?php

namespace Flames\Orm\Database\QueryBuilder;

use Flames\Collection\Arr;
use Flames\Environment;
use PDO;
use Exception;

/**
 * @internal
 */
class MySql extends DefaultEx
{
    protected $mode = 'table';
    protected $connection;

    protected $model = null;
    protected $modelData = null;
    protected $modelCast = null;
    protected $table = null;

    protected $wheres = [];

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function setTable($table)
    {
        $this->mode = 'table';
        $this->table = $table;
        $this->model = null;
    }

    public function setModel($model)
    {
        $this->mode = 'model';
        $this->modelData = $model::getMetadata();
        $this->modelCast = \Flames\Orm\Database\Cast\Factory::getByDatabaseType(
            \Flames\Orm\Database\DataFactory::getConfigByDatabase($this->modelData->database)->type
        );
        $this->table = $this->modelData->table;
    }

    public function where($keyOrFunction, $valueOrCondition, $value = null)
    {
        $argsCount = func_num_args();
        if ($argsCount === 2) {
            return $this->_where('AND', $keyOrFunction, $valueOrCondition);
        }

        return $this->_where('AND', $keyOrFunction, $valueOrCondition, $value);
    }

    public function orWhere($keyOrFunction, $valueOrCondition, $value = null)
    {
        $argsCount = func_num_args();
        if ($argsCount === 2) {
            return $this->_where('OR', $keyOrFunction, $valueOrCondition);
        }

        return $this->_where('OR', $keyOrFunction, $valueOrCondition, $value);
    }

    protected function _where($operator, $keyOrFunction, $valueOrCondition, $value = null)
    {
        $function = null;
        $key = null;
        $condition = '=';

        if (is_callable($keyOrFunction) === true) {
            $valueOrCondition = null;
            $value = null;
            $function = $keyOrFunction;
        }
        elseif (is_string($keyOrFunction) === true) {
            $argsCount = func_num_args();
            $key = $keyOrFunction;

            if ($argsCount === 3) {
                $value = $valueOrCondition;
            } else {
                $condition = $valueOrCondition;
                $value = $value;
            }
        }
        else {
            throw new Exception('Invalid where parameters in table ' . $this->table  . '.');
        }

        // TODO: $function parenthesis
//        if ($function !== null) {
//
//        } else {

        if ($this->mode === 'model') {
            if (isset($this->modelData->column[$key]) === false) {
                throw new \Exception('Model key ' . $key . ' not found in class ' . $class);
            }

            $data = $this->castDataPre([$key => $value]);
            $this->modelData->column[$key]->name;

            $this->wheres[] = [
                'type' => 'default',
                'key' => $this->modelData->column[$key]->name,
                'condition' => $condition,
                'value' => $value,
                'operator' => $operator
            ];
        } else {
            $this->wheres[] = [
                'type' => 'default',
                'key' => $key,
                'condition' => $value,
                'value' => $value
            ];
        }
//        }

        return $this;
    }

    public function whereLike($key, $value = null)
    {
        return $this->_whereLike('AND', $key, $value);
    }

    public function orWhereLike($key, $value = null)
    {
        return $this->_whereLike('OR', $key, $value);
    }

    protected function _whereLike($operator, $key, $value = null)
    {
        if ($this->mode === 'model') {
            if (isset($this->modelData->column[$key]) === false) {
                throw new \Exception('Model key ' . $key . ' not found in class ' . $class);
            }

            $data = $this->castDataPre([$key => $value]);
            $this->modelData->column[$key]->name;

            $this->wheres[] = [
                'type' => 'default',
                'key' => $this->modelData->column[$key]->name,
                'condition' => 'LIKE',
                'value' => $value,
                'operator' => $operator
            ];
        } else {
            $this->wheres[] = [
                'type' => 'default',
                'key' => $key,
                'condition' => 'LIKE',
                'value' => $value
            ];
        }

        return $this;
    }

    protected function _nativeWhere($data)
    {
        $query = '';

        if (count($this->wheres) > 0) {
            $query .= ' WHERE ';
            $firstWhere = true;
            $whereIndex = 0;


            foreach ($this->wheres as $where) {
                if ($firstWhere === true) {
                    $firstWhere = false;
                } else {
                    $query .= (' ' . $where['operator'] . ' ');
                }

                if ($where['type'] === 'default') { // TODO: function/raw
                    $whereParam = (':where_' . $whereIndex);

                    if ($where['condition'] === 'LIKE') {
                        $whereParam = (
                            'CONCAT(\'%\', ' .
                            $whereParam .
                            ', \'%\')'
                        );
                    }

                    $query .= ('`' . $where['key'] . '` ' . $where['condition'] . ' ' . $whereParam . ' ');
                }

                $data['where_' . $whereIndex] = $where['value'];
                $whereIndex++;
            }

            $query = (substr($query, 0, -1) . "\r\n");
        }

        return ['data' => $data, 'query' => $query];
    }

    public function get()
    {
        $data = [];
        $query = ('SELECT * FROM `' . $this->table . '` ');

        $nativeWhere = $this->_nativeWhere($data);
        $data = $nativeWhere['data'];
        $query .= $nativeWhere['query'];

        $statement = $this->connection->prepare($query);
        $statement->execute($data);

        if ($this->mode === 'model') {
            $models = Arr();
            while ($row = $statement->fetch()) {
                $modelData = [];
                foreach ($this->modelData->column as $column) {
                    if (isset($row[$column->name]) === true) {
                        $modelData[$column->property] = $row[$column->name];
                    }
                }

                $modelData = $this->castDataPos($modelData, true);
                $models[] = new $this->modelData->class($modelData, true);
            }

            return $models;
        }

        $rows = Arr();
        while ($row = $statement->fetch()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function update($data)
    {
        if ($this->mode === 'model') {
            $data = $this->castDataPos($data);
            $data = $this->castDataPre($data);
        }

        if (count($data) === 0) {
            throw new Exception('Update payload in table ' . $this->table . ' can\'t be empty.');
        }

        $query = ('UPDATE `' . $this->table . '` SET ');

        if ($this->mode === 'model') {
            foreach ($data as $key => $value) {
                $query .= ('`' . $this->modelData->column[$key]->name . '` = :' . $key . ',');
            }
        } else {
            foreach ($data as $key => $_) {
                $query .= ('`' . $key . '` = :' . $key . ',');
            }
        }

        $query = (substr($query, 0, -1) . "\r\n");

        $nativeWhere = $this->_nativeWhere($data);
        $data = $nativeWhere['data'];
        $query .= $nativeWhere['query'];

        $statement = $this->connection->prepare($query);
        $statement->execute($data);
        return true;
    }

    public function insert($data)
    {
        if ($this->mode === 'model') {
            $data = $this->castDataPos($data);
            $data = $this->castDataPre($data);
        }

        if (count($data) === 0) {
            throw new Exception('Insert payload in table ' . $this->table  . ' can\'t be empty.');
        }

        $query = ('INSERT INTO `' . $this->table . '` ' . "\n\t(");

        if ($this->mode === 'model') {
            foreach ($data as $key => $_) {
                $query .= ('`' . $this->modelData->column[$key]->name . '`, ');
            }
        } else {
            foreach ($data as $key => $_) {
                $query .= ('`' . $key . '`, ');
            }
        }

        $query = (substr($query, 0, -2) . ")\nVALUES (");
        foreach ($data as $key => $_) {
            $query .= (':' . $key . ', ');
        }
        $query = (substr($query, 0, -2) . ');');

        $statement = $this->connection->prepare($query);
        $statement->execute($data);

        $insertId = $this->connection->lastInsertId();

        if ($this->mode === 'model') {
            $autoIncrementColumn = false;
            foreach ($this->modelData->column as $column) {
                if ($column->autoIncrement === true) {
                    $autoIncrementColumn = $column;
                    break;
                }
            }

            if ($autoIncrementColumn !== null) {
                $cast = $this->modelCast;
                return Arr([
                    $autoIncrementColumn->property => $cast::pos($autoIncrementColumn, $insertId)
                ]);
            }
        }

        return $insertId;
    }

    protected function castDataPos($data, $fromDb = false)
    {
        $cast = $this->modelCast;

        foreach ($data as $key => $value) {
            if (isset($this->modelData->column[$key]) === false) {
                throw new \Exception('Model key ' . $key . ' not found in class ' . $this->modelData->class);
            }

            $data[$key] = $cast::pos($this->modelData->column[$key], $value, $fromDb);
        }

        return $data;
    }

    protected function castDataPre($data)
    {
        $cast = $this->modelCast;

        foreach ($data as $key => $value) {
            if (isset($this->modelData->column[$key]) === false) {
                throw new \Exception('Model key ' . $key . ' not found in class ' . $this->modelData->class);
            }
            $data[$key] = $cast::pre($this->modelData->column[$key], $value);
        }

        return $data;
    }
}
