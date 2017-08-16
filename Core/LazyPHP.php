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

        Session::set('error', $e);
        header('Location: /error');
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
        '<div>';

    if ($displayBacktrace) {
        $html .=
            '<ol>';
        $backtraces = debug_backtrace();
        foreach ($backtraces as $backtrace) {
            $html .=
                '<li><strong>'.$backtrace['file'].'</strong> '.$backtrace['line'].'</li>';
        }
        $html .=
            '</ol>';
    }

    $html .=
            '<pre>'.print_r($data, true).'</pre>'.
        '</div>';

    echo $html;
}
