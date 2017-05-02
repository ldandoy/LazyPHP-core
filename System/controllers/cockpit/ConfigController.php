<?php

namespace System\controllers\cockpit;

use app\controllers\cockpit\CockpitController;

use System\Config;
use System\Router;
use System\Session;

class ConfigController extends CockpitController
{
    /**
     * @var System\Config
    */
    public $config = null;

    public function indexAction()
    {
        if ($this->config === null) {
            $this->config = Config::getAll();
        }

        $this->render('index', array(
            'config'        => $this->config,
            'formConfig'    => url('cockpit_system_config_save'),
            'titlePage'     => '<i class="fa fa-columns"></i> Gestion de la configuration',
        ));
    }

    public function saveAction()
    {
        // var_dump($this->request->post);
        $ini = "; Ceci est le fichier de configuration\n; Les commentaires commencent par ';', comme dans php.ini\n\n";
        foreach ($this->request->post['config'] as $key => $value) {
            $ini .= "[".$key."]"."\n";
            foreach ($value as $key1 => $value1) {
                $ini .= $key1." = \"".$value1."\"\n";
            }
            $ini .= "\n";
        }

        // Write the ini file
        $fp = fopen(CONFIG_DIR.DS."config.ini", 'w');
        fwrite($fp, $ini);
        fclose($fp);

        // $this->indexAction();
    }
}
