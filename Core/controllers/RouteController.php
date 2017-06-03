<?php

/**
 * File Core\controllers\RouteController.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
namespace Core\controllers;

use app\controllers\FrontController;

/**
 * Class manage routes of the application
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class RouteController extends FrontController
{

    public $layout = "error";

    /**
     * indexAction
     *
     * Show the list of the route
     *
     * @return void
     */
    public function indexAction()
    {
        $this->render('index', array(
            'routes'  => $this->routes
        ));
    }
}
