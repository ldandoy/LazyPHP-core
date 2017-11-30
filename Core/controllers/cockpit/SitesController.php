<?php

namespace Core\controllers\cockpit;

use app\controllers\cockpit\CockpitController;
use Core\models\Site;
use Core\Router;
use Core\Session;

class SitesController extends CockpitController
{
    /**
     * @var Core\models\Site
     * _site because $this->site exists in parent "Controller" class
     */
    private $_site = null;

    /**
     * @var string
     */
    private $pageTitle = '<i class="fa fa-snowflake-o"></i> Gestion des sites';

    public function indexAction()
    {
        $siteClass = $this->loadModel('Site');
        $sites = $siteClass::findAll();

        $this->render(
            'core::sites::index',
            array(
                'sites' => $sites,
                'pageTitle' => $this->pageTitle,
                'boxTitle' => 'Liste des sites'
            )
        );
    }

    public function showAction($id)
    {
        $siteClass = $this->loadModel('Site');
        $this->_site = $siteClass::findById($id);

        $this->render(
            'core::sites::show',
            array(
                'site' => $this->_site,
                'pageTitle' => $this->pageTitle,
                'boxTitle' => 'Fiche du site : '.$this->_site->label,
            )
        );
    }

    public function newAction()
    {
        $siteClass = $this->loadModel('Site');
        if (!isset($this->_site)) {
            $this->_site = new $siteClass();
        }

        $themeOptions = $siteClass::getThemesOptions();

        $this->render(
            'core::sites::edit',
            array(
                'pageTitle' => $this->pageTitle,
                'boxTitle' => 'Nouveau site',
                'site' => $this->_site,
                'formAction' => Router::url('cockpit_core_sites_create')
            )
        );
    }

    public function editAction($id)
    {
        $siteClass = $this->loadModel('Site');
        if (!isset($this->_site)) {
            $this->_site = $siteClass::findById($id);
        }

        $themeOptions = $siteClass::getThemesOptions();

        $this->render(
            'core::sites::edit',
            array(
                'pageTitle' => $this->pageTitle,
                'boxTitle' => 'Edition du site: '.$this->_site->label,
                'site' => $this->_site,
                'themeOptions' => $themeOptions,
                'formAction' => Router::url('cockpit_core_sites_update_'.$id)
            )
        );
    }

    public function createAction()
    {
        $siteClass = $this->loadModel('Site');
        $this->_site = new $siteClass();

        if (!isset($this->request->post['active'])) {
            $this->request->post['active'] = 0;
        }

        if ($this->_site->save($this->request->post)) {
            $this->addFlash('Site ajouté', 'success');
            $this->redirect('cockpit_core_sites_index');
        } else {
            $this->addFlash('Erreur(s) dans le formulaire', 'danger');
        }

        $this->newAction();
    }

    public function updateAction($id)
    {
        $siteClass = $this->loadModel('Site');
        $this->_site = $siteClass::findById($id);

        if (!isset($this->request->post['active'])) {
            $this->request->post['active'] = 0;
        }

        if ($this->_site->save($this->request->post)) {
            $this->addFlash('Site modifiée', 'success');
            if ($this->current_user->site_id === null) {
                $this->redirect('cockpit_core_sites_index');
            } else {
                $this->redirect('cockpit_core_sites_show_'.$this->current_user->site_id);
            }
        } else {
            $this->addFlash('Erreur(s) dans le formulaire', 'danger');
        }

        $this->editAction($id);
    }

    public function deleteAction($id)
    {
        $siteClass = $this->loadModel('Site');
        $site = $siteClass::findById($id);
        $site->delete();
        $this->addFlash('Site supprimé', 'success');
        $this->redirect('cockpit_core_sites_index');
    }

    public function changehostAction()
    {
        $siteClass = $this->loadModel('Site');
        $site = $siteClass::findById($this->request->post['site_id']);
        if ($site !== null) {
            $this->session->set('site', $site);
            $this->redirect($this->request->post['redirect']);
        }
    }
}
