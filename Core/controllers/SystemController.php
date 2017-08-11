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

use Core\Controller;

/**
 * Class manage routes of the application
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class SystemController extends Controller
{

    public $layout = 'error';

    /**
     * routesAction
     *
     * Show the list of routes
     *
     * @return void
     */
    public function routesAction()
    {
        $this->render(
            'core::system::routes',
            array(
                'routes'  => $this->routes
            )
        );
    }

    /**
     * errorAction
     *
     * When an error or an exception is thrown, go here
     *
     * @return void
     */
    public function errorAction()
    {
        $exception = $this->session->getAndRemove('error');
        $error = $exception !== null ? $exception->getMessage() : '';

        $this->render(
            'core::system::error',
            array(
                'error' => $error
            )
        );
    }
}
