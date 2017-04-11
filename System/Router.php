<?php
/**
 * File system\Router.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

/**
 * Class gérant les requètes arrivant au serveur
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Router
{
    public static $routes;

    /**
     * Init, load all routes defined for the site (in route.ini)
     *
     * @return void
     */
    public static function init()
    {
        $defaultController = Config::getValueG('controller');
        $defaultAction = Config::getValueG('action');
        $adminPrefix = Config::getValueG('admin_prefix');

        /* The default root */
        self::$routes['defaults_index']['url'] = '/'.$defaultController.'/'.$defaultAction;
        self::$routes['defaults_index']['controller'] = $defaultController;
        self::$routes['defaults_index']['action'] = $defaultAction;
        self::$routes['defaults_index']['method'] = 'get';
        self::$routes['defaults_index']['prefix'] = null;
        self::$routes['defaults_index']['package'] = null;

        /* The default root admin */
        self::$routes[$adminPrefix.'_defaults_index']['url'] = '/'.$adminPrefix.'/'.$defaultController.'/'.$defaultAction;
        self::$routes[$adminPrefix.'_defaults_index']['prefix'] = $adminPrefix;
        self::$routes[$adminPrefix.'_defaults_index']['controller'] = $defaultController;
        self::$routes[$adminPrefix.'_defaults_index']['action'] = $defaultAction;
        self::$routes[$adminPrefix.'_defaults_index']['method'] = 'get';
        self::$routes[$adminPrefix.'_defaults_index']['package'] = null;

        $routeConfs = parse_ini_file(CONFIG_DIR.DS.'route.ini', true);

        /* On charge les routes du fichier ini */
        foreach ($routeConfs as $section => $params) {
            self::createRoute($section, $params);
        }

        foreach (Config::$packages as $key => $packages) {
            $route_ini_path = VENDOR_DIR.DS.$packages.DS.'config/route.ini';
            if (file_exists($route_ini_path)) {
                $routeConf = parse_ini_file($route_ini_path, true);
                foreach ($routeConf as $section => $params) {
                    self::createRoute($section, $params, $key);
                }
            }
        }
        /*echo "<pre>";
        var_dump(self::$routes);
        echo "</pre>";*/
    }

    private static function createRoute($section, $params, $package = '')
    {
        $method = isset($params['method']) ? $params['method'] : 'get';
        $crudActions = array(
            'index' => 'get',
            'new' => 'get',
            'create' => 'post',
            'show' => 'get',
            'edit' => 'get',
            'update' => 'post',
            'delete' => 'post'
        );

        switch ($method) {
            case 'crud':
                foreach ($crudActions as $actionName => $actionMethod) {
                    // On détecte le prefixe
                    $key = $section.'_'.$actionName;
                    if (strpos($section, Config::getValueG('admin_prefix')) === 0) {
                        $prefix = Config::getValueG('admin_prefix');
                        self::$routes[$key]['url'] = '/'.str_replace('_', '/', $key);
                        $infos = explode("_", $section);
                        if (isset($params['parent'])) {
                            self::$routes[$key]['url'] = preg_replace("/".$params['parent']."/", $params['parent']."/:".$params['parent']."_id", self::$routes[$key]['url'], 1);
                            $controller = $infos[sizeof($infos)-1];
                        } else {
                            $controller = $infos[sizeof($infos)-1];
                        }
                        self::$routes[$key]['url'] = str_replace($prefix, $prefix.'/'.$package, self::$routes[$key]['url']);
                    } else {
                        $prefix = '';
                        $controller = $section;
                        self::$routes[$key]['url'] = '/'.str_replace('_', '/', $key);
                    }

                    if (in_array($actionName, ['show', 'edit', 'update', 'delete'])) {
                        self::$routes[$key]['url'] = self::$routes[$key]['url']."/:id";
                    }

                    self::$routes[$key]['method'] = $actionMethod;
                    self::$routes[$key]['prefix'] = $prefix;
                    self::$routes[$key]['package'] = $package;
                    self::$routes[$key]['controller'] = $controller;
                    self::$routes[$key]['action'] = $actionName;
                }
                break;

            case 'post':
            case 'get':
            default:
                $key = $section;
                self::$routes[$key]['url'] = $params['url'];
                self::$routes[$key]['method'] = $method;
                self::$routes[$key]['prefix'] = $params['prefix'];
                self::$routes[$key]['controller'] = $params['controller'];
                self::$routes[$key]['action'] = $params['action'];
                self::$routes[$key]['package'] = $package;
                break;
        }
    }

    /**
     * Parse la requète
     *
     * On parse la requète et on ajoute les infos (prefix, controller, action, params) à la l'obj $request
     *
     * @param system\Request $request la requête
     *
     * @return bool
     */
    public static function parse($request)
    {
        $urlFound = false;
        foreach (self::$routes as $k => $route) {
            if ($request->url == $route['url']) {
                $key = $k;
                $urlFound = true;
            }

            if (!$urlFound) {
                $search = '/^'.str_replace('/', '\/', $route['url']).'/';
                $search = preg_replace('/:([a-z0-9\-_]+)/', '(?P<${1}>[a-z0-9\-_]+)', $search);
                preg_match($search, $request->url, $match);
                if (!empty($match)) {
                    $key = $k;
                    $urlFound = true;
                    foreach ($match as $k => $v) {
                        if (!is_numeric($k)) {
                            $params[$k] = $v;
                        }
                    }
                }
            }
        }

        if ($urlFound) {
            $route = self::$routes[$key];
            $request->prefix = $route['prefix'];
            $request->package = $route['package'];
            $request->controller = ucfirst($route['controller']);
            $request->action = $route['action'];

            if (isset($params) && !empty($params)) {
                $request->params = $params;
            }
        }

        return $urlFound;
    }

    /**
     * Decode an url
     *
     * @param string $url
     *
     * @return mixed
     */
    /*public static function decodeUrl($url)
    {
        $res = array();

        $adminPrefix = Config::getValueG('admin_prefix');

        $tabUrl = deleteEmptyItem(explode('/', $url));

        $controller = array_shift($tabUrl);

        if ($controller == $adminPrefix) {
            $res['prefix'] = $adminPrefix;
            $controller = array_shift($tabUrl);
        }

        if (isset(Config::$packages[$controller])) {
            $res['package'] = $controller;
            $controller = array_shift($tabUrl);
        }

        if ($controller === null) {
            $controller = $defaultController;
        }
        $res['controller'] = $controller;

        $action = array_shift($tabUrl);
        if ($action === null) {
            $action = $defaultAction;
        }
        $res['action'] = $action;

        if (!empty($tabUrl)) {
            $res['params'] = $tabUrl;
        }


        (isset($a['prefix']) ? $a['prefix'].'_' : '').(isset($a['package']) ? $a['package'].'_' : '').$a['controller'].'_'.$a['action'];
        return $res;
    }*/

    /**
     * Convert a string to an URL
     *
     * @param string $string The string
     * @param mixed $params Parameters to add to the URL
     *
     * @return string
     */
    public static function url($string, $params = array())
    {
        $url = '/'.ltrim(str_replace('_', '/', $string), '/');
        if (!empty($params)) {
            $url .= '/'.implode('/', $params);
        }
        return $url;
    }
}
