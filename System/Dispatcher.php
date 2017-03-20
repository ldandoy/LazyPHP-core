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
    public $request = null;
    public $controller = null;

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
        
        $controller = $this->loadController();
        $action = $this->request->action.'Action';
        if (!in_array($action, get_class_methods($controller))) {
            $this->error('Action error', 'Method "'.$action.'" was not found in controller "'.$this->controller.'".');
        }
        call_user_func_array(array($controller, $action), $this->request->params);
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
        $file = CONTROLLER_DIR.DS.$this->controller.'Controller.php';

        if (is_file($file)) {
            $class = '\app\\controllers\\'.str_replace('/', '\\', $this->controller)."Controller";
            if (class_exists($class)) {
                
                $controller = new $class($this->request);
                return $controller;
            } else {
                $this->error('Controller error', 'Controller "'.$class.'" was not found.');
            }
        } else {
            $this->error('Controller error', 'File "'.$file.'" doesn\'t exist.');
        }
    }

    public function error($titre, $message)
    {
        $controller = new Controller($this->request);
        $controller->e404($titre, $message);
    }
}
