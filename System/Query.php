<?php
/**
 * File System\Query.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

/**
 * Class for managing queries text
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Query
{
    /**
     * The query type ('select' | 'insert' | 'update' | 'delete')
     *
     * @var string
     */
    private $queryType;

    /**
     * The query's sql text
     *
     * @var string
     */
    private $sql = '';

    /**
     * The select part of the query
     *
     * @var string
     */
    private $select = '';

    /**
     * From part of the query
     *
     * @var string
     */
    private $from = '';

    /**
     * Join part of the query
     *
     * @var string[]
     */
    private $join = array();

    /**
     * The where part of the query
     *
     * @var string[]
     */
    private $where = array();

    /**
     * The order part of the query
     *
     * @var string[]
     */
    private $order = array();

    /**
     * The insert part of the query
     *
     * @var string
     */
    private $insert = '';

    /**
     * The update part of the query
     *
     * @var string
     */
    private $update = '';

    /**
     * The delete part of the query
     *
     * @var string
     */
    private $delete = '';

    /**
     * The prepared query
     *
     * @var \PDOStatement
     */
    private $preparedStatement = null;

    /*
     * @var string
     */
    public $lastError = '';

    /**
     * Set the query type and reset parts of the query
     *
     * @param string $queryType
     */
    private function setQueryType($queryType)
    {
        $this->queryType = $queryType;
        $this->sql = '';
        $this->select = '';
        $this->from = '';
        $this->join = array();
        $this->where = array();
        $this->order = array();
        $this->insert = '';
        $this->update = '';
        $this->delete = '';
        $this->preparedStatement = null;
    }

    /**
     * Create the select part of the query
     *
     * @param mixed $columns 'col1,col2,...' | array('col1', 'col2', ...)
     *
     * @return \System\Query
     */
    public function select($columns = '*')
    {
        $this->setQueryType('select');

        if (is_array($columns)) {
            $this->select = 'SELECT '.explode(',', $columns);
        } else {
            $this->select = 'SELECT '.$columns;
        }

        return $this;
    }

    /**
     * Create the from part of the query
     *
     * @param string $table
     * @param string $alias
     *
     * @return \System\Query
     */
    public function from($table, $alias = '')
    {
        $this->from = 'FROM '.$table.rtrim(' '.$alias);
        return $this;
    }

    /**
     * Create the join part of the query
     *
     * @param mixed $join
     *     array(
     *         'jointure' => 'LEFT JOIN' | 'RIGHT JOIN' | ...
     *         'table' => 'table1'
     *         'fkey_table' => 'table2'
     *         'fkey_column' => 'col'
     *     )
     *
     * @return \System\Query
     */
    public function join($join)
    {
        if (is_array($join)) {
            $sqlJoin =
                $join['jointure'].' '.
                $join['table'].' '.
                'ON '.$join['table'].'.id = '.
                $join['fkey_table'].'.'.$join['fkey_column'];
        } else {
            $sqlJoin = $join;
        }

        $this->join[] = $sqlJoin;

        return $this;
    }

    /**
     * Create the where part of the query
     *
     * @param mixed $join
     *     array(
     *         'column' => 'col'
     *         'operator' => '=' | '>' | ...
     *         'value' => 'val'
     *     )
     *
     * @return \System\Query
     */
    public function where($where = '')
    {
        if (is_array($where)) {
            $this->where[] = $where['column'].' '.$where['operator'].' '.$where['value'];
        } else {
            if ($where != '') {
                $this->where[] = $where;
            }
        }

        return $this;
    }

    /**
     * Create the order part of the query
     *
     * @param mixed $order
     *     array(
     *         'column' => 'col'
     *         'order' => 'asc' | 'desc'
     *     )
     *
     * @return \System\Query
     */
    public function order($order = '')
    {
        if (is_array($order)) {
            $this->order[] = $order['column'].' '.$order['order'];
        } else {
            if ($order != '') {
                $this->order[] = $order;
            }
        }

        return $this;
    }

    /**
     * Create the insert part of the query
     *
     * @param mixed $params
     *     array(
     *         'table' => 'table',
     *         'columns' => array('col1', 'col2,...)
     *     )
     *
     * @return \System\Query
     */
    public function insert($params = array())
    {
        $this->setQueryType('insert');

        $sqlParams = [];
        foreach ($params['columns'] as $c) {
            $sqlParams[] = ':'.$c;
        }

        $this->insert =
            'INSERT INTO '.$params['table'].'('.
            implode(',', $params['columns']).
            ') VALUES ('.
            implode(',', $sqlParams).
            ')';

        return $this;
    }

    /**
     * Create the update part of the query
     *
     * @param mixed $params
     *     array(
     *         'table' => 'table',
     *         'columns' => array('col1', 'col2,...)
     *     )
     *
     * @return \System\Query
     */
    public function update($params = array())
    {
        $this->setQueryType('update');

        $sqlColumnsParams = array();
        foreach ($params['columns'] as $c) {
            $sqlColumnsParams[] = $c.' = :'.$c;
        }

        $this->update =
            'UPDATE '.$params['table'].' SET '.
            implode(',', $sqlColumnsParams);

        return $this;
    }

    /**
     * Create the delete part of the query
     *
     * @param mixed $params
     *
     * @return void
     */
    public function delete($params = array())
    {
        $this->setQueryType('delete');

        $this->delete = 'DELETE FROM '.$params['table'];
    }

    /**
     * Create the sql text with all part of the query
     *
     * Create the sql text with all part of the query and put it in $this->sql
     *
     * @return void
     */
    public function createSql()
    {
        switch ($this->queryType) {
            case 'select':
                if (count($this->join) > 0) {
                    $join = implode(' ', $this->join);
                } else {
                    $join = '';
                }

                if (count($this->where) > 0) {
                    $where = 'WHERE '.implode(' AND ', $this->where);
                } else {
                    $where = '';
                }

                if (count($this->order) > 0) {
                    $order = ' ORDER BY '.implode(', ', $this->order);
                } else {
                    $order = '';
                }

                $this->sql =
                    $this->select.' '.
                    $this->from.' '.
                    ltrim($join.' ').
                    $where.
                    $order;

                break;

            case 'insert':
                $this->sql = $this->insert;
                break;

            case 'update':
                if (count($this->where) > 0) {
                    $where = 'WHERE '.implode(' AND ', $this->where);
                } else {
                    $where = 'WHERE id = 0';
                }

                $this->sql =
                    $this->update.' '.
                    $where;
                break;

            case 'delete':
                if (count($this->where) > 0) {
                    $where = 'WHERE '.implode(' AND ', $this->where);
                } else {
                    $where = 'WHERE id = 0';
                }

                $this->sql =
                    $this->delete.' '.
                    $where;
        }
    }

    /**
     * Check if the query text is not null
     *
     * If $this->sql is null call the createSql to create the sql text.
     *
     * @return void
     */
    private function checkCreateSql()
    {
        if ($this->sql == '') {
            $this->createSql();
        }
    }

    /**
     * Return the sql text of the query
     *
     * @return string $this->sql the sql text of the query
    */
    public function getSql()
    {
        $this->checkCreateSql();
        return $this->sql;
    }

    /**
     * Print the the sql text of the query
     *
     * @return void
     */
    public function showSql()
    {
        $this->checkCreateSql();
        debug($this->sql);
    }

    /**
     * Execute the query
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function execute($data = array())
    {
        $this->checkCreateSql();

        $this->lastError = '';

        $res = Db::prepare($this->sql);
        if ($res !== false) {
            $this->preparedStatement = $res;

            foreach ($data as $k => $v) {
                Db::bind($this->preparedStatement, $k, $v);
            }

            if ($this->preparedStatement->execute()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Execute the query and fetch all rows
     *
     * @param mixed $params
     *
     * @return mixed
     */
    public function executeAndFetchAll($params = array())
    {
        $this->lastError = '';

        if ($this->execute($params)) {
            return $this->fetchAll();
        } else {
            return false;
        }
    }

    /**
     * Fetch all rows
     *
     * @return mixed|bool
     *
     */
    public function fetchAll()
    {
        $this->lastError = '';

        if ($this->preparedStatement !== null) {
            return $this->preparedStatement->fetchAll(Db::FETCH_OBJ);
        } else {
            return false;
        }
    }

    /**
     * Execute the query and fetch all rows
     *
     * @param mixed $params
     *
     * @return mixed|bool
     */
    public function executeAndFetch($params = array())
    {
        $this->lastError = '';

        if ($this->execute($params)) {
            return $this->fetch();
        } else {
            return false;
        }
    }

    /**
     * Fetch one row
     *
     * @return mixed
     */
    public function fetch()
    {
        return $this->preparedStatement->fetch(Db::FETCH_OBJ);
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public function lastInsertId()
    {
        return Db::lastInsertId();
    }
}
