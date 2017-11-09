<?php
/**
 * File Core\Db.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

/**
 * Class gérant les connextion à la base de données
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Db
{
    /**
     * @var \PDO
     */
    static public $db;

    const FETCH_OBJ = \PDO::FETCH_OBJ;

    /**
     * Prepare a sql query on the server
     * @param string $sql
     * @return \PDOStatement | bool
     */
    public static function prepare($sql)
    {
        if (!isset(self::$db)) {
            try {
                self::$db = new \PDO(
                    'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset='.DB_CHARSET,
                    DB_USER,
                    DB_PASSWORD
                );

                self::$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new \Exception('PDOException (PDO::__construct) : '.$e->getMessage(), $e->getCode());
                return false;
            }
        }

        try {
            return self::$db->prepare($sql);
        } catch (PDOException $e) {
            throw new \Exception('PDOException (PDO::prepare) : '.$e->getMessage(), $e->getCode());
            return false;
        }
    }

    /**
     * Bind a parameter
     * @param \PDOStatement $statement
     * @param string $param
     * @param mixed $value
     * @return bool
     */
    public static function bind($statement, $param, $value)
    {
        try {
            if ($value === null) {                
                return $statement->bindValue(':'.$param, $value, \PDO::PARAM_NULL);
            } else {
                return $statement->bindValue(':'.$param, $value);
            }
        } catch (PDOException $e) {
            throw new \Exception('PDOException (PDOStatement::bindParam) : '.$e->getMessage(), $e->getCode());
            return false;
        }
    }

    /**
     * Get last insert id
     *
     * @return int
     */
    public static function lastInsertId()
    {
        return self::$db->lastInsertId();
    }
}
