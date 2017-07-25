<?php
/**
 * File Core\Request.php
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

    public $site_id = null;

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

        $this->host = $_SERVER['HTTP_HOST'];

        // On set le site id dans la session
        if (Config::getValueG('multisite') != null) {
            if (Session::get('site_id') === null) {
                $site = Site::findBy('host', $this->host);
                if (!empty($site)) {
                    Session::set('site_id', $site->id);
                    $this->site_id = $site->id;
                }
            } else {
                $this->site_id = Session::get('site_id');
                $site = Site::findById($this->site_id);
            }
        }



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

            if (isset(Config::$packages[$controller])) {
                $package = $controller;
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

            $this->url = '/'.(isset($prefix) ? $prefix.'/' : '').(isset($package) ? $package.'/' : '').$controller.'/'.$action.(count($params) > 0 ? '/'.implode('/', $params) : '');
            $this->format = 'html';
        } else {
            /* If the url is just / */
            if (isset($site)) {
                $this->url = $site->root_path;
            } else { // Sinon on prend le root
                $this->url = Config::getValueG('root');
            }
            $this->format = 'html';
        }

        /* We manage the request method */
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        /* We manage the request params */
        $sessionPost = Session::getAndRemove('post');
        if ($sessionPost !== null) {
            $_POST = array_merge($_POST, $sessionPost);
        }

        if (isset($_FILES) && !empty($_FILES)) {
            $files = array();
            foreach ($_FILES as $key => $file) {
                if (is_array($file['name'])) {
                    for ($i = 0; $i < count($file['name']); $i++) {
                        if ($file['name'][$i] != '') {
                            foreach ($file as $k => $v) {
                                $files[$key][$i][$k] = $v[$i];
                            }
                        }
                    }
                    // foreach ($file as $k1 => $v1) {
                    //     foreach ($v1 as $k2 => $v2) {
                    //         $files[$key][$k2][$k1] = $v2;
                    //     }
                    // }
                } else {
                    if ($file['name'] != '') {
                        $files[$key] = array($file);
                    }
                }
            }
            $_POST = array_merge($_POST, $files);
        }

        $this->post = $_POST;
    }
}
