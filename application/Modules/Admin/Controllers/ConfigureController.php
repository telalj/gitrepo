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
 * @package    Admin
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: cnfigureController.php 4 2009-6-1 Jaimie $
 */
class Admin_ConfigureController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $accountType  = null;

    /* @access Public
     * @var object
     */
    private static $auth         = null;

    /* @access Public
     * @var object
     */
    private static $acl          = null;

    /* @access Public
     * @var object
     */
    private static $boxDb        = null;

    /* @access Public
     * @var object
     */
    private static $boxForm      = null;

    /* @access Public
     * @var object
     */
    private static $modulelForm  = null;

    /* @access Public
     * @var object
     */
    private static $moduleConfig = null;

    /* @access Public
     * @var object
     */
    private static $config      = null;
    
    
    /** 
     * @access Public
     * @return void
     */ 
    public function init()
    {       
        // load registry
        $registry = Zend_Registry::getInstance();

        self::$config = $registry->get('moduleConfig')->module->admin;
        self::$acl    = $this->view->acl;        
        self::$auth   = $this->view->auth;
        
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

        if(!self::$acl->isAllowed($accountType, null, 'admin') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/admin:configure');
        }    

        self::$boxDb         = new Model_Box_Db;

        self::$boxForm       = new Model_Box_Form;

        self::$modulelForm   = new Model_Module_Form;
    
        self::$moduleConfig  = Zend_Registry::get('moduleConfig');      
    }
    
    
    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        $data = Zend_Registry::get('configuration');

        $form = self::$modulelForm->mainConfiguration($data);

        // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                 $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->config->email->type           = $values['emailType'];
                $config->default->config->email->from->email    = $values['emailFromEmail'];
                $config->default->config->mail->from->name      = $values['emailFromName'];
                if($values['emailType'] == 'smtp') {
                    $auth = true;
                } else {
                    $auth = false;       
                }
                $config->default->config->email->auth           = $auth;
                $config->default->config->email->user           = $values['emailUser'];
                $config->default->config->email->pass           = $values['emailPass'];
                $config->default->config->email->host           = $values['emailHost'];
                $config->default->config->email->admin          = $values['emailAdmin'];
                $config->default->config->dateformat            = $values['dateformat'];
                $config->default->config->timeformat            = $values['timeformat'];
                if( !empty($values['lastFMKey']) ) {
                    $useAudioscrobbler = 1;  
                } else {
                    $useAudioscrobbler = 0;
                }
                $config->default->media->useAudioscrobbler      = $useAudioscrobbler;
                $config->default->media->lastFMKey              = $values['lastFMKey'];
                $config->default->media->downLoadImages         = $values['downLoadImages'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
        
            }
        } else {
            $this->view->form = $form;
        }        
    }
   

    /** 
     * @access Public
     * @return void
     */
    public function boxesAction()
    {
        $data = self::$boxDb->getBoxes();
    
        $form = self::$boxForm->editForm($data);

        // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
    
        
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function modulesAction()
    {
        $moduleConfig = Zend_Registry::get('moduleConfig');

        $this->view->moduleConfig = $moduleConfig->module;
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function editModuleAction()
    {
        $moduleConfig = Zend_Registry::get('moduleConfig');

        
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function editModuleDefaultAction()
    {
        $module = self::$moduleConfig->module->default;

        $form   = self::$modulelForm->defaultForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->default->layout               = $values['layout'];                
                $config->default->module->default->cache->enable        = $values['cache_enabled'];
                $config->default->module->default->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->default->cache->serialization = $values['cache_serialization'];
                $config->default->module->default->perPage              = $values['per_page'];
                $config->default->module->default->pageRange            = $values['page_range'];
                $config->default->module->default->acl->Guest           = $values['guest_Acl'];
                $config->default->module->default->acl->Member          = $values['member_Acl'];
                $config->default->module->default->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleSearchAction()
    {
        $module = self::$moduleConfig->module->search;

        $form   = self::$modulelForm->searchForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->search->layout               = $values['layout'];                
                $config->default->module->search->cache->enable        = $values['cache_enabled'];
                $config->default->module->search->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->search->cache->serialization = $values['cache_serialization'];
                $config->default->module->search->perPage              = $values['per_page'];
                $config->default->module->search->pageRange            = $values['page_range'];
                $config->default->module->search->acl->Guest           = $values['guest_Acl'];
                $config->default->module->search->acl->Member          = $values['member_Acl'];
                $config->default->module->search->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModulePlayAction()
    {
        $module = self::$moduleConfig->module->play;

        $form   = self::$modulelForm->playForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->play->layout               = $values['layout'];                
                $config->default->module->play->cache->enable        = $values['cache_enabled'];
                $config->default->module->play->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->play->cache->serialization = $values['cache_serialization'];
                $config->default->module->play->perPage              = $values['per_page'];
                $config->default->module->play->pageRange            = $values['page_range'];
                $config->default->module->play->acl->Guest           = $values['guest_Acl'];
                $config->default->module->play->acl->Member          = $values['member_Acl'];
                $config->default->module->play->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleAdminAction()
    {
        $module = self::$moduleConfig->module->admin;

        $form   = self::$modulelForm->adminForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->admin->layout               = $values['layout'];                
                $config->default->module->admin->cache->enable        = $values['cache_enabled'];
                $config->default->module->admin->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->admin->cache->serialization = $values['cache_serialization'];
                $config->default->module->admin->perPage              = $values['per_page'];
                $config->default->module->admin->pageRange            = $values['page_range'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function editModuleAccountAction()
    {
        $module = self::$moduleConfig->module->account;

        $form   = self::$modulelForm->accountForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));
                
                $config->default->module->account->layout               = $values['layout'];                
                $config->default->module->account->cache->enable        = $values['cache_enabled'];
                $config->default->module->account->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->account->cache->serialization = $values['cache_serialization'];
                $config->default->module->account->perPage              = $values['per_page'];
                $config->default->module->account->pageRange            = $values['page_range'];
                $config->default->module->account->register             = $values['register'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');

            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleArtistAction()
    {
        $module = self::$moduleConfig->module->artist;
    
        $form   = self::$modulelForm->artistForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
            
                 $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->artist->enabled              = $values['enabled'];
                $config->default->module->artist->layout               = $values['layout'];                
                $config->default->module->artist->cache->enable        = $values['cache_enabled'];
                $config->default->module->artist->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->artist->cache->serialization = $values['cache_serialization'];
                $config->default->module->artist->perPage              = $values['per_page'];
                $config->default->module->artist->pageRange            = $values['page_range'];
                $config->default->module->artist->api                  = $values['api'];
                $config->default->module->artist->rss                  = $values['rss'];
                $config->default->module->artist->acl->Guest           = $values['guest_Acl'];
                $config->default->module->artist->acl->Member          = $values['member_Acl'];
                $config->default->module->artist->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleAlbumAction()
    {
        $module = self::$moduleConfig->module->album;

        $form   = self::$modulelForm->albumForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->album->enabled              = $values['enabled'];
                $config->default->module->album->layout               = $values['layout'];                
                $config->default->module->album->cache->enable        = $values['cache_enabled'];
                $config->default->module->album->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->album->cache->serialization = $values['cache_serialization'];
                $config->default->module->album->perPage              = $values['per_page'];
                $config->default->module->album->pageRange            = $values['page_range'];
                $config->default->module->album->api                  = $values['api'];
                $config->default->module->album->rss                  = $values['rss'];
                $config->default->module->album->acl->Guest           = $values['guest_Acl'];
                $config->default->module->album->acl->Member          = $values['member_Acl'];
                $config->default->module->album->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleFileAction()
    {
        $module = self::$moduleConfig->module->file;

        $form   = self::$modulelForm->fileForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->file->layout               = $values['layout'];                
                $config->default->module->file->cache->enable        = $values['cache_enabled'];
                $config->default->module->file->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->file->cache->serialization = $values['cache_serialization'];
                $config->default->module->file->perPage              = $values['per_page'];
                $config->default->module->file->pageRange            = $values['page_range'];
                $config->default->module->file->acl->Guest           = $values['guest_Acl'];
                $config->default->module->file->acl->Member          = $values['member_Acl'];
                $config->default->module->file->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();
        
                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleContentAction()
    {
        $module = self::$moduleConfig->module->content;

        $form   = self::$modulelForm->contentForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->content->layout               = $values['layout'];                
                $config->default->module->content->cache->enable        = $values['cache_enabled'];
                $config->default->module->content->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->content->cache->serialization = $values['cache_serialization'];
                $config->default->module->content->perPage              = $values['per_page'];
                $config->default->module->content->pageRange            = $values['page_range'];
                $config->default->module->content->acl->Guest           = $values['guest_Acl'];
                $config->default->module->content->acl->Member          = $values['member_Acl'];
                $config->default->module->content->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();
    
                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function editModuleGenreAction()
    {
        $module = self::$moduleConfig->module->genre;

        $form   = self::$modulelForm->genreForm($module);

         // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->genre->enabled              = $values['enabled'];
                $config->default->module->genre->layout               = $values['layout'];                
                $config->default->module->genre->cache->enable        = $values['cache_enabled'];
                $config->default->module->genre->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->genre->cache->serialization = $values['cache_serialization'];
                $config->default->module->genre->perPage              = $values['per_page'];
                $config->default->module->genre->pageRange            = $values['page_range'];
                $config->default->module->genre->api                  = $values['api'];
                $config->default->module->genre->rss                  = $values['rss'];
                $config->default->module->genre->acl->Guest           = $values['guest_Acl'];
                $config->default->module->genre->acl->Member          = $values['member_Acl'];
                $config->default->module->genre->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }

    
    public function editModulePictureAction()
    {
        $module = self::$moduleConfig->module->picture;

        $form   = self::$modulelForm->pictureForm($module);

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->module->picture->layout               = $values['layout'];                
                $config->default->module->picture->cache->enable        = $values['cache_enabled'];
                $config->default->module->picture->cache->lifetime      = $values['cache_lifetime'];
                $config->default->module->picture->cache->serialization = $values['cache_serialization'];
                $config->default->module->picture->perPage              = $values['per_page'];
                $config->default->module->picture->pageRange            = $values['page_range'];
                $config->default->module->picture->api                  = $values['api'];
                $config->default->module->picture->rss                  = $values['rss'];
                $config->default->module->picture->acl->Guest           = $values['guest_Acl'];
                $config->default->module->picture->acl->Member          = $values['member_Acl'];
                $config->default->module->picture->acl->PowerMember     = $values['power_member_Acl'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Module.ini'));
                $writer->write();

                $this->_redirect('admin/configure/modules-complete');
            }
        } else {
            $this->view->form = $form;
        }

    }


    public function editModuleInstallAction()
    {

    }


    /** 
     * @access Public
     * @return void
     */
    public function modulesCompleteAction()
    {

    }

}

