<?php
/**
 * File system\Request.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

use System\Session;

/**
 * Class Request
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Request
{
    /**
     * @var string
     */
    public $url;

    /**
     * @var mixed
     */
    public $params;

    /**
     * @var mixed
     */
    public $post = null;

    /**
     * @var string get|post
     */
    public $method = 'get';

    /**
     * @var string html|json
     */
    public $format = 'html';

    /**
     * Constructor
     *
     * Getting the url to create an array with prefix, controller, action, params, method infos
     *
     * @return void
     */
    public function __construct()
    {
        $defaultController = Config::getValueG('controller');
        $defaultAction = Config::getValueG('action');

        /* We manage the request info */
        if (isset($_SERVER['PATH_INFO'])) {
            $url = $_SERVER['PATH_INFO'];

            $adminPrefix = Config::getValueG('admin_prefix');

            $tabUrl = deleteEmptyItem(explode('/', $url));
            $controller = array_shift($tabUrl);
            if ($controller == $adminPrefix) {
                $prefix = $adminPrefix;
                $controller = array_shift($tabUrl);
            }
            $action = array_shift($tabUrl);
            $params = $tabUrl;

            if ($controller === null) {
                $controller = $defaultController;
            }

            if ($action === null) {
                $action = $defaultAction;
            }

            $this->url = '/'.(isset($prefix) ? $prefix.'/' : '').$controller.'/'.$action.(count($params) > 0 ? '/'.implode('/', $params) : '');
            $this->format = 'html';
        } else {
            /* If the url is just / */
            $this->url = '/'.$defaultController.'/'.$defaultAction;
            $this->format = 'html';
        }
        /* We manage the request method */
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        /* We manage the request params */
        $sessionPost = Session::getAndRemove('post');
        if ($sessionPost !== null) {
            $_POST = array_merge($_POST, $sessionPost);
        }
        $this->post = $_POST;
    }
}
