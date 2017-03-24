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
        $routeConfs = parse_ini_file(CONFIG_DIR.DS.'route.ini', true);

        $crudActions = array(
            'index' => 'get',
            'new' => 'get',
            'create' => 'post',
            'show' => 'get',
            'edit' => 'get',
            'update' => 'post',
            'delete' => 'delete'
        );

        $defaultController = Config::getValueG('controller');
        $defaultAction = Config::getValueG('action');
        $adminPrefix = Config::getValueG('admin_prefix');

        /* The default root */
        $routes['defaults_index']['url'] = '/'.$defaultController.'/'.$defaultAction;
        $routes['defaults_index']['controller'] = $defaultController;
        $routes['defaults_index']['action'] = $defaultAction;
        $routes['defaults_index']['method'] = 'get';

        /* The default root admin */
        $routes[$adminPrefix.'_defaults_index']['url'] = '/'.$adminPrefix.'/'.$defaultController.'/'.$defaultAction;
        $routes[$adminPrefix.'_defaults_index']['prefix'] = $adminPrefix;
        $routes[$adminPrefix.'_defaults_index']['controller'] = $defaultController;
        $routes[$adminPrefix.'_defaults_index']['action'] = $defaultAction;
        $routes[$adminPrefix.'_defaults_index']['method'] = 'get';

        /* On charge les routes du fichier ini */
        foreach ($routeConfs as $section => $params) {
            $method = isset($params['method']) ? $params['method'] : 'get';

            switch ($method) {
                case 'crud':
                    foreach ($crudActions as $actionName => $actionMethod) {
                        $key = $section.'_'.$actionName;
                        $routes[$key]['url'] = '/'.str_replace('_', '/', $key);
                        $routes[$key]['method'] = $actionMethod;
                        $routes[$key] = array_merge(
                            $routes[$key],
                            self::decodeUrl($routes[$key]['url'])
                        );
                    }
                    break;

                case 'post':
                case 'get':
                default:
                    $key = $section;
                    $routes[$key]['url'] = $params['url'];
                    $routes[$key]['method'] = $method;
                    $routes[$key] = array_merge(
                        $routes[$key],
                        self::decodeUrl($routes[$key]['url'])
                    );
                    break;
            }
        }
        self::$routes = $routes;
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
        $a = self::decodeUrl($request->url);

        $key = 
            (isset($a['prefix']) ? $a['prefix'].'_' : '').
            (isset($a['package']) ? $a['package'].'_' : '').
            $a['controller'].'_'.
            $a['action'];

        foreach (self::$routes as $k => $v) {
            if ($key == $k) {
                $route = $v;

                if (isset($route['prefix'])) {
                    $request->prefix = $route['prefix'];
                }

                if (isset($route['package'])) {
                    $request->package = $route['package'];
                }

                $request->controller = ucfirst($route['controller']);
                $request->action = $route['action'];

                if (isset($a['params'])) {
                    $request->params = $a['params'];
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Decode an url
     *
     * @param string $url
     *
     * @return mixed
     */
    public static function decodeUrl($url)
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

        return $res;
    }

    /**
     * Convert a string to an URL
     *
     * @param string $string The string
     * @param mixed $params Parameters to add to the URL
     *
     * @return string
     */
    public static function url($string = null, $params = array())
    {
        $url = '/'.ltrim(str_replace('_', '/', $string), '/');
        if (!empty($params)) {
            $url .= '/'.implode('/', $params);
        }
        return $url;
    }
}
