<?php

namespace Core;

use Core\Session;
use Core\Config;
use Core\Router;
use Core\Dispatcher;

class LazyPHP
{
    /**
     * @var int
     */
    public static $startTime = 0;

    /**
     * @var int
     */
    public static $endTime = 0;

    /**
     * @var Core\Dispatcher
     */
    private static $dispatcher = null;

    public static function init()
    {
        spl_autoload_register(function($class) {
            $class = str_replace('\\', '/', $class);
            $file = ROOT_DIR.DS.$class.'.php';
            if (file_exists($file)) {
                require_once $file;
            }
        });

        Session::init();
        Config::init();
        Router::init();
    }

    public static function run()
    {
        self::$startTime = microtime(true);

        try {
            self::init();
            self::$dispatcher = new Dispatcher();
            self::$dispatcher->run();
        } catch (\Exception $e) {
            self::error($e);
        }

        self::$endTime = microtime(true);
    }

    public static function error($e)
    {
        if (self::$dispatcher !== null) {
            if (self::$dispatcher->request !== null) {
                Session::set('errorUrl', self::$dispatcher->request->url);
                if (self::$dispatcher->request->format == 'json') {
                    header('Content-Type: application/json');
                    echo json_encode(
                        array(
                            'error' => true,
                            'message' => $e->getMessage()
                        )
                    );
                    return;
                }
            }
        }

        if (Session::get('error') === null) {
            Session::set('error', $e);
            header('Location: /error');
        } else {
            echo $e->getMessage();
            exit;
        }
    }
}

/**
 * Display debug info
 *
 * @param mixed $data
 * @param bool $displayBacktrace
 *
 * @return void
 */
function debug($data, $displayBacktrace = true)
{
    $html =
        '<pre>';

    if ($displayBacktrace) {
        $html .=
            '<ol>';
        $backtraces = debug_backtrace();
        foreach ($backtraces as $backtrace) {
            $cta = 
                (isset($backtrace['class']) ? $backtrace['class'] : '').
                (isset($backtrace['type']) ? $backtrace['type'] : '').
                (isset($backtrace['function']) ? $backtrace['function'] : '');
            if ($cta == '') {
                $cta = '?';
            }

            if (isset($backtrace['file'])) {
                $html .=
                    '<li><strong>'.$backtrace['file'].'</strong> '.$backtrace['line'].' : '.$cta.'</li>';
            } else {
                $html .= '<li>'.$cta.'</li>';
            }
        }
        $html .=
            '</ol>';
    }

    $html .=
            print_r($data, true).
        '</pre>';

    echo $html;
}

$appWidgetsDir = APP_DIR.DS.'widgets';
if ($handle = opendir($appWidgetsDir)) {
    while (false !== ($entry = readdir($handle))) {
        if(!is_dir($appWidgetsDir.DS.$entry)) {
            require $appWidgetsDir.DS.$entry;
        }
    }
}

$widgetsDir = ROOT_DIR.DS.'vendor'.DS.'overconsulting'.DS.'lazyphp-widget'.DS.'Widget'.DS.'widgets';
if ($handle = opendir($widgetsDir)) {
    while (false !== ($entry = readdir($handle))) {
        if(!is_dir($widgetsDir.DS.$entry) && $entry != 'Widget.php') {
            require $widgetsDir.DS.$entry;
        }
    }
}
