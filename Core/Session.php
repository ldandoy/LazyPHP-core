<?php
/**
 * File Core\Session.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

/**
 * Class to manage session
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Session
{
    public static $session = null;

    /**
     * Init
     *
     * @return \Core\Session
     */
    public static function init()
    {
        session_start();
        self::$session = new Session();
    }

    public function __call($name, $arguments)
    {
        $class = get_called_class();
        if (method_exists($class, $name)) {
            call_user_func_array(array($class, $name), $arguments);
        }
    }

    /**
     * Add/Set a session variable
     *
     * @return void
     */
    public static function set($name, $value = null)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Get a session variable
     *
     * @return mixed
     */
    public static function get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * Get all session variable
     *
     * @return mixed
     */
    public static function getAll()
    {
        return isset($_SESSION) ? $_SESSION : null;
    }

    /**
     * Remove a session variable
     *
     * @return void
     */
    public static function remove($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Remove a session variable
     *
     * @return void
     */
    public static function removeAll()
    {
        unset($_SESSION);
    }

    /**
     * Get and remove a session variable
     *
     * @return mixed
     */
    public static function getAndRemove($name)
    {
        $value = self::get($name);
        self::remove($name);
        return $value;
    }

    /**
     * Get the session id
     * @return string
     */
    public static function getSessionId()
    {
        return session_id();
    }
}
