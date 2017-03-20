<?php
/**
 * File system\Db.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace system;

use \PDO;

/**
 * Class gérant les connextion à la base de données
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Db
{
    static public $db;
    const FETCH_OBJ = PDO::FETCH_OBJ;

    /**
    * Prepare a sql query on the server
    * @param string $sql
    * @return \PDOStatement
    */
    public static function prepare($sql)
    {
        if (!isset(self::$db)) {
            try {
                self::$db = new PDO(
                    'mysql:host='.Config::getValueDB('URL').';dbname='.Config::getValueDB('DB').';charset='.Config::getValueDB('CHARSET'),
                    Config::getValueDB('USER'),
                    Config::getValueDB('PASSWORD')
                );

                self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            } catch (PDOException $e) {
                die('Erreur : '.$e->getMessage());
                return false;
            }
        }

        try {
            return self::$db->prepare($sql);
        } catch (PDOException $e) {
            die('Erreur : '.$e->getMessage());
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
            return $statement->bindParam(':'.$param, $value);
        } catch (PDOException $e) {
            die('Erreur : '.$e->getMessage());
            return false;
        }
    }
}
