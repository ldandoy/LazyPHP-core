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

        $this->checkUrl();

        if (isset($this->request->prefix)) {
            $this->controller = $this->request->prefix.DS.$this->request->controller;
        } else {
            $this->controller = $this->request->controller;
        }

        if (isset($this->request->package)) {
            $this->package = $this->request->package;
        }

        $controller = $this->loadController();
        $action = $this->request->action.'Action';
        $params = isset($this->request->params) ? $this->request->params : array();
        if (!in_array($action, get_class_methods($controller))) {
            $this->error('Action error', 'Method "'.$action.'" was not found in controller "'.$this->controller.'".');
        }
        call_user_func_array(array($controller, $action), $params);
    }

    public function checkUrl()
    {
        if (!isset($this->request->controller)) {
            $this->error('Controller error', 'Controller not found or invalid');
        }

        if (!isset($this->request->action)) {
            $this->error('Action error', 'Action not found or invalid');
        }
    }

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

    public function error($titre, $message)
    {
        die($titre.'<br />'.$message);
        // $controller = new Controller(null);
        // $controller->e404($titre, $message);
    }
}
