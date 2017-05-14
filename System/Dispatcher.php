<?php
/**
 * File system\Dispatcher.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

use MultiSite\models\Site;

/**
 * Class qui appel le bon controller en fonction de la bonne url.
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Dispatcher
{
    /**
     * @var System\Request
     */
    public $request = null;

    /**
     * @var string
     */
    public $controller = null;

    /**
     * @var string
     */
    public $package = null;

    public function __construct()
    {
        $this->request = new Request();

        if (!Router::parse($this->request)) {
            $this->error('URL error', 'Requested URL was not found.');
        }

        // $this->checkUrl();

        $this->controller = $this->request->controller;
        if (isset($this->request->prefix) && $this->request->prefix != '') {
            $this->controller = $this->request->prefix.'\\'.$this->controller;
        }

        if (isset($this->request->package) && $this->request->package != '') {
            $this->package = $this->request->package;
        }

        $controller = $this->loadController();
        if (method_exists($controller, 'before')) {
            $controller->before();
        }

        $action = $this->request->action.'Action';
        if (!in_array($action, get_class_methods($controller))) {
            $this->error('Action error', 'Method "'.$action.'" was not found in controller "'.$this->controller.'".');
        }

        $params = isset($this->request->params) ? $this->request->params : array();

        call_user_func_array(array($controller, $action), $params);
        if (method_exists($controller, 'after')) {
            $controller->after();
        }
    }

    /*public function checkUrl()
    {
        if (!isset($this->request->controller)) {
            $this->error('Controller error', 'Controller not found or invalid');
        }

        if (!isset($this->request->action)) {
            $this->error('Action error', 'Action not found or invalid');
        }
    }*/

    public function loadController()
    {
        if (isset($this->package)) {
            // $dir = VENDOR_DIR.DS.Config::$packages[$this->package].DS.'controllers';
            $namespace = '\\'.ucfirst($this->package);
        } else {
            // $dir = CONTROLLER_DIR;
            $namespace = '\\app';
        }

        // $file = $dir.DS.$this->controller.'Controller.php';
        $class = $namespace.'\\controllers\\'.$this->controller.'Controller';

        if (class_exists($class)) {
            $controller = new $class($this->request);
            return $controller;
        } else {
            $this->error('Controller error', 'Controller "'.$class.'" was not found.');
        }
    }

    public function error($title, $message)
    {
        die($title.'<br />'.$message);
    }
}
