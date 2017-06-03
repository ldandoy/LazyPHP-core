<?php
/**
 * File Core\Dispatcher.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

use MultiSite\models\Site;

/**
 * Class qui appel le bon controller en fonction de la bonne url.
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Dispatcher
{
    /**
     * @var Core\Request
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

    public function loadController()
    {
        $controllerName = $this->controller;

        // On regarde si le fichier est dans /app/modeles
        $classController = '\\app\\controllers\\'.$controllerName.'Controller';
        if (class_exists($classController)) {
            $controllerName = new $classController($this->request);
            return $controllerName;
        }

        // on regade si le fichier est dans le /package/models/
        $classController = '\\'.ucfirst($this->package).'\\controllers\\'.$controllerName.'Controller';
        if (class_exists($classController)) {
            $controllerName = new $classController($this->request);
            return $controllerName;
        }

        $this->error('Controller error', 'Controller "'.$controllerName.'" was not found.');
    }

    public function error($title, $message)
    {
        die($title.'<br />'.$message);
    }
}
