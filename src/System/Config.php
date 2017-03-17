<?php
/**
 * File system\Config.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

/**
 * Class gérant la configuration de l'application
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Config
{
    public static $config;
    public static $config_db;
    public static $config_general;
    public static $config_css;
    public static $config_js;

    /**
     * Init
     *
     * Read the config file and set debug and report properties
     *
     * @return void
     */
    public static function init()
    {
        /*self::$config =  parse_ini_file(CONFIG_DIR.DS."config.ini", true);
        self::$config_db = self::$config['DB'];
        self::$config_general = self::$config['GENERAL'];
        self::$config_css = self::$config['CSS'];
        self::$config_js = self::$config['JS'];

        if (!ini_get('display_errors')) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', self::getValueG('debug'));
        }*/
    }

    /**
     * Return an item of the DB configuration
     *
     * Example: Config::getValueDB('host')
     *
     * @param string $name Item of the DB configuration
     *
     * @return string value of the item of the DB configuration
     */
    public static function getValueDB($name)
    {
        return self::$config_db[$name];
    }

    /**
     * Return an item of the general configuration
     *
     * Example: Config::getValueG('debug')
     *
     * @param string $name Item of the general configuration
     *
     * @return string value of the item of the general configuration
     */
    public static function getValueG($name)
    {
        return self::$config_general[$name];
    }
}
