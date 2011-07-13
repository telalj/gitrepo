<?php
/**
 * VooDoo Music Box
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.voodoomusicbox.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@voodoomusicbox.com so we can send you a copy immediately.
 *
 * @category   VooDoo Music Box
 * @package    Picture
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: EditController.php 4 2009-6-1 Jaimie $
 */
class Picture_EditController extends Zend_Controller_Action
{

    /* @access Public
     * @var object
     */
    private static $artistDb   = null;

    /* @access Public
     * @var object
     */
    private static $artistForm   = null;

    /* @access Public
     * @var object
     */
    private static $acl        = null;

    /* @access Public
     * @var object
     */
    private static $auth       = null;

    /* @access Public
     * @var object
     */
    private static $config     = null;

    
    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        // load registry
        $registry = Zend_Registry::getInstance();
        
        // if module is active
        if ( !$registry->get('moduleConfig')->module->picture->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Picture');
        }

        self::$artistDb   = new Model_Artist_Db;

        self::$artistForm = new Model_Artist_Form;

        self::$acl        = $this->view->acl; 

        self::$auth        = $this->view->auth;

        self::$config      = $registry->get('moduleConfig')->module->picture;
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function artistImageAction()
    {
        /** Enable Dojo */
        $this->view->dojo()->enable()
            ->setDjConfigOption('parseOnLoad', true)
            ->requireModule('dijit.Tooltip');

        /** Acls */
        if(self::$auth->hasIdentity()) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }
        
        foreach (self::$config->acl as $key => $val) {
            $resourceArray = explode(':', $val);
            self::$acl->allow($key,  null,   $resourceArray);            
        }    

        if(!self::$acl->isAllowed($accountType, null, 'update') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/picture:edit:artist-image');
        } 

        // load image
        $imageId = (int)$this->getRequest()->getParam('id');

        if($imageId < 1 ) {
            $this->_redirect('error/artist-image-not-found');
        }
        $image = self::$artistDb->getArtistImageById($imageId);
        $this->view->image = $image;

        // load artist
        $artist = self::$artistDb->getArtistByID($image['artist_id']);
        $this->view->artist = $artist;

        // breadcrumb
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Home'), 'url' => $this->view->baseUrl);
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Artist'), 'url' => 'artist/');
        $breadcrumbs[] = array('title' => $artist['artist'], 'url' => 'artist/view/'.urlencode($artist['artist']));
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Pictures'), 'url' => 'artist/pictures/' . urlencode($artist['artist']));
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Edit_Pictures'), 'url' => $this->view->baseUrl);

        $this->view->placeholder('breadcrumbs')->exchangeArray( Helper_Breadcrumb::process($breadcrumbs) );
      
        // get form
        $form = self::$artistForm->artistImageEdit($image);
        
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                $data = array(
                    'title' => $values['title']
                );

                self::$artistDb->updateArtistImage($data, $imageId);

                $this->_redirect('picture/edit/complete/id/'.$imageId);
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function completeAction()
    {
        /** Enable Dojo */
        $this->view->dojo()->enable()
            ->setDjConfigOption('parseOnLoad', true)
            ->requireModule('dijit.Tooltip');

        /** Acls */
        if(self::$auth->hasIdentity()) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }
        
        foreach (self::$config->acl as $key => $val) {
            $resourceArray = explode(':', $val);
            self::$acl->allow($key,  null,   $resourceArray);            
        }    

        if(!self::$acl->isAllowed($accountType, null, 'update') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/picture:edit:artist-image');
        } 

        // load image
        $imageId = (int)$this->getRequest()->getParam('id');
        if($imageId < 1 ) {
            $this->_redirect('error/artist-image-not-found');
        }
        $image = self::$artistDb->getArtistImageById($imageId);
        $this->view->image = $image;

        // load artist
        $artist = self::$artistDb->getArtistByID($image['artist_id']);
        $this->view->artist = $artist;

        // breadcrumb
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Home'), 'url' => $this->view->baseUrl);
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Artist'), 'url' => 'artist/');
        $breadcrumbs[] = array('title' => $artist['artist'], 'url' => 'artist/view/'.urlencode($artist['artist']));
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Pictures'), 'url' => 'artist/pictures/' . urlencode($artist['artist']));
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Edit_Pictures'), 'url' => $this->view->baseUrl);

        $this->view->placeholder('breadcrumbs')->exchangeArray( Helper_Breadcrumb::process($breadcrumbs) );
      

    }
}

