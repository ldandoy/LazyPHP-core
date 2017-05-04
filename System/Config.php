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

    public static $packages = array();

    /**
     * Init
     *
     * Read the config file and set debug and report properties
     *
     * @return void
     */
    public static function init()
    {
        self::$config =  parse_ini_file(CONFIG_DIR.DS."config.ini", true);

        if (!ini_get('display_errors')) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', self::getValueG('debug'));
        }

        foreach (self::$config["PACKAGES"] as $key => $value) {
            if (is_file(VENDOR_DIR.DS.$value."/config/config.ini")) {
                $package_config = parse_ini_file(VENDOR_DIR.DS.$value."/config/config.ini", true);
                self::$config = array_merge_recursive(self::$config, $package_config);
            }
        }

        self::$config_db = self::$config['DB'];
        self::$config_general = self::$config['GENERAL'];
        self::$config_css = self::$config['CSS'];
        self::$config_js = self::$config['JS'];
        self::$packages = self::$config['PACKAGES'];

        spl_autoload_register(function ($class) {
            $class = str_replace('\\', '/', $class);
            $file = ROOT_DIR.DS.$class.'.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });
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
        if (isset(self::$config_general[$name])) {
            return self::$config_general[$name];
        } else {
            return null;
        }
    }

    public static function getAll()
    {
        return parse_ini_file(CONFIG_DIR.DS."config.ini", true);
    }

    public static function getSite($id)
    {
        $config = parse_ini_file(CONFIG_DIR.DS."config.ini", true);
        return $config['SITE'.$id];
    }
}
