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
            $this->config = new Config();
        }

        $this->render('index', array(
            'config' => $config,
            'pageTitle' => '<i class="fa fa-columns"></i> Paramètres du site',
        ));
    }

    public function saveAction($id)
    {
        $this->config = new Config();
        $this->config->setData($this->request->post);

        if ($this->config->valid()) {
            if ($this->config->save() {
                Session::addFlash('Paramètres enregistrés', 'success');
                $this->redirect('cockpit_systems_config');
            } else {
                Session::addFlash('Erreur mise à jour base de données', 'danger');
            }
        } else {
            Session::addFlash('Erreur(s) dans le formulaire', 'danger');
        }

        $this->indexAction();
    }
}
