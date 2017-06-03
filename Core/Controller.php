<?php
/**
 * File Core\Controller.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

use Core\Session;
use Core\Templator;
use Core\Router;

/**
 * Class gérant les Controllers du site
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Controller
{
    public $request;
    public $controller;
    public $routes = null;
    public $config = null;
    public $sessions = null;
    public $layout = null;

    public function __construct($request)
    {
        $this->request = $request;
        if (isset($this->request->controller)) {
            if (isset($this->request->prefix)) {
                $this->controller = $this->request->prefix.DS.strtolower($this->request->controller);
            } else {
                $this->controller = strtolower($this->request->controller);
            }
        }

        $this->routes = Router::$routes;
        $this->config = Config::$config;
        $this->session = Session::getAll();
    }

    /**
     * @param string $view
     *
     * @return string|bool
     */
    private function findView($view)
    {
        $viewArray = explode("::", $view);

        switch (count($viewArray)) {
            case 1:
                $package = "app";
                $directory = "defaults";
                $tplName = $viewArray[0];
                break;

            case 2:
                $package = "app";
                $directory = $viewArray[0];
                $tplName = $viewArray[1];
                break;

            case 3:
                $package = $viewArray[0];
                $directory = $viewArray[1];
                $tplName = $viewArray[2];
                break;
            
            default:
                $package = "app";
                $directory = "";
                $tplName = $viewArray[0];
                break;
        }

        if ($package == "app") {
            if ($this->request->prefix == "") {
                $tplFile = VIEW_DIR.DS.$directory.DS.$tplName.'.php';
            } else {
                $tplFile = VIEW_DIR.DS.$this->request->prefix.DS.$directory.DS.$tplName.'.php';
            }
            if (file_exists($tplFile)) {
                return $tplFile;
            }
        } else {
            $package = $this->config["PACKAGES"][$package];
            $tplFile = VENDOR_DIR.DS.$package.DS.'views'.DS.$this->request->prefix.DS.$directory.DS.$tplName.'.php';
            if (file_exists($tplFile)) {
                return $tplFile;
            }
        }

        foreach ($this->config["PACKAGES"] as $package) {
            $tplFile = VENDOR_DIR.DS.$package.DS.'views'.DS.$this->request->prefix.DS.$directory.DS.$tplName.'.php';
            if (file_exists($tplFile)) {
                return $tplFile;
            }
        }

        return false;
    }

    /**
     * Render the view
     *
     * @param string $view
     * @param mixed $params
     * @param bool $layout
     *
     * @return string
     */
    public function render($view, $params = array(), $layout = true)
    {
        $tpl = $this->findView($view);
        if ($tpl) {
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $$key = $value;
                }
            }
            ob_start();
            require_once $tpl;
            $yeslp = ob_get_clean();
        } else {
            $message = 'Le template "'.DS.$this->controller.DS.$view.'.php" n\'existe pas';
            $this->error('Erreur de template', $message);
        }

        if ($layout) {
            ob_start();
            $layout = $this->loadLayout();
            require_once $layout;
            $html = ob_get_clean();
        } else {
            $html = $yeslp;
        }

        $templator = new Templator();
        $html = $templator->parse($html, $params);

        echo $html;

        Session::remove('redirect');
    }

    public function error($title, $message)
    {
        die($title.'<br />'.$message);
    }

    public function redirect($url, $code = null)
    {
        $redirect = Session::getAndRemove('redirect');
        if ($redirect === null || $redirect != $url) {
            if ($code == 301) {
                header('HTTP/1.1 301 Move Permanently');
            }
            Session::set('redirect', $url);
            //Session::set('post', $this->request->post);
            header('Location: '.Router::url($url));
            exit;
        }
    }

    public function loadCss()
    {
        // CSS dans bower -> bower_components
        foreach (Config::$config_css as $value) {
            echo "<link rel=\"stylesheet\" href=\"/bower_components/".$value."\" />\n";
        }

        // CSS qui sont dans les dossiers assets
        if (file_exists(CSS_DIR)) {
            if ($handle = opendir(CSS_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != '.' && $entry != '..' && !is_dir(CSS_DIR.DS.$entry)) {
                        echo "<link rel=\"stylesheet\" href=\"/assets".DS."css".DS.$entry."\" />\n";
                    }
                }
                closedir($handle);
            }
        }

        foreach (Config::$packages as $packageName => $package) {
            $dir = ASSETS_DIR.DS.$packageName.DS.'css';
            $cssDir = '/assets/'.$packageName.'/css';

            if (file_exists($dir)) {
                if ($handle = opendir($dir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                            echo '<link rel="stylesheet" href="'.$cssDir.'/'.$entry.'" />';
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

    public function loadJs()
    {
        // JS dans bower -> bower_components
        foreach (Config::$config_js as $value) {
            echo '<script src="/bower_components/'.$value.'" type="text/javascript"></script>';
        }

        // Script qui sont dans les dossiers assets
        if (file_exists(JS_DIR)) {
            if ($handle = opendir(JS_DIR)) {
                while (false !== ($entry = readdir($handle))) {
                    if ($entry != "." && $entry != "..") {
                        echo '<script src="/assets'.DS.'js'.DS.$entry.'" type="text/javascript"></script>';
                    }
                }
                closedir($handle);
            }
        }

        foreach (Config::$packages as $packageName => $package) {
            $dir = ASSETS_DIR.DS.$packageName.DS.'js';
            $jsDir = '/assets/'.$packageName.'/js';

            if (file_exists($dir)) {
                if ($handle = opendir($dir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != "..") {
                            echo '<script src="'.$jsDir.'/'.$entry.'" type="text/javascript"></script>';
                        }
                    }
                    closedir($handle);
                }
            }
        }
    }

    private function stripAccents($str)
    {
        return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
    }

    public function loadModel($modelName)
    {
        // On regarde si le fichier est dans /app/modeles
        $classModel = '\\app\\models\\'.$modelName;
        if (class_exists($classModel)) {
            $modelName = new $classModel();
            return $classModel;
        }

        // on regade si le fichier est dans le /package/models/
        $classModel = '\\'.ucfirst($this->request->package).'\\models\\'.$modelName;
        if (class_exists($classModel)) {
            $modelName = new $classModel();
            return $classModel;
        }


        // Sinon on retourne une erreur
        $this->error('Model error', 'Model "'.$modelName.'" was not found.');
    }

    public function loadLayout()
    {
        // Check the prefix
        if (!isset($this->request->prefix) || $this->request->prefix === null) {
            $prefix = "";
        } else {
            $prefix = $this->request->prefix;
        }

        // Check if there is a layout, ifnot we use base.html
        if (!isset($this->layout) || $this->layout === null) {
            $this->layout = 'base';
        }

        // We check if the file existe in app/view/layout
        $layout = VIEW_DIR.DS.'layout'.DS.$prefix.DS.$this->layout.'.php';
        if (file_exists($layout)) {
            return $layout;
        }

        // We use the one of Core package
        $layout = VENDOR_DIR.DS.$this->config["PACKAGES"]['core'].DS.'views'.DS.'layout'.DS.$this->layout.'.php';
        if (file_exists($layout)) {
            return $layout;
        }

        $message = 'Le layout "'.$layout.'" n\'existe pas';
        $this->error('Erreur de layout', $message);
    }
}
