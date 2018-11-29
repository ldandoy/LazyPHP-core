<?php

namespace Core\controllers;

use Core\Controller;

class SystemController extends Controller
{

    public $layout = 'login';

    /**
     * routesAction
     *
     * Show the list of routes
     *
     * @return void
     */
    public function routesAction()
    {
        if (defined('DEBUG') && DEBUG) {
            $this->render(
                'core::system::routes',
                array(
                    'routes'  => $this->routes
                )
            );
        } else {
            $this->redirect('/');
        }
    }

    public function maintenanceAction () {
        $this->render(
            'core::system::maintenance',
            array(
                'altImageLogin' => 'Default Image Login',
                'imageLogin' => $this->site->brand_logo->url,
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
        $error = '';
        if (defined('DEBUG') && DEBUG) {
            $exception = $this->session->getAndRemove('error');
            $error = $exception !== null ? $exception->getMessage() : '';
        }

        $url = $this->session->getAndRemove('errorUrl');

        $this->render(
            'core::system::error',
            array(
                'error' => $error,
                'url' => $url
            )
        );
    }
}
