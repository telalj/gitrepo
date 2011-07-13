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
 * @version    $Id: ContentController.php 4 2009-6-1 Jaimie $
 */
class Admin_ContentController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $contentDb   = null;
    
    /* @access Public
     * @var object
     */
    private static $contentForm = null;

    /* @access Public
     * @var object
     */
    private static $auth        = null;

    /* @access Public
     * @var object
     */
    private static $acl         = null;

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


        self::$contentDb     = new Model_Content_Db;

        self::$contentForm   = new Model_Content_Form;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        // page number from request
        $page = $this->getRequest()->getParam('page');

        // build paginated data
        $paginator = self::$contentDb->getPages($page);
        $this->view->paginator = $paginator;        
    }


    /** 
     * @access Public
     * @return void
     */
    public function addAction()
    {
        $form = self::$contentForm->addForm();

            // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $find = array("/[^a-zA-Z0-9\s]/","/\s+/");
                $replace = array(" ","-");

                $contentPage = strtolower(preg_replace($find,$replace,$values['content_page']));

                $contentFile = $contentPage.'-'.Zend_Registry::get('language');

                $config      = Zend_registry::get('configuration');
                $siteRootDir = Zend_registry::get('siteRootDir');

                $contentDir = $siteRootDir . $config->content->pagePath.'/index';
                                        

                if ( !$fh = fopen($contentDir.'/'.$contentFile.'.phtml', 'w') ) {
                    throw new exception('Error can not open ' . $contentDir.'/'.$contentFile . ' for writing.');
                }
        
                fwrite( $fh, $values['content_description_text'] );
                fclose($fh);

                $data = array (
                        'content_page'           => $contentPage,
                        'content_build_time'     => time(),
                        'content_in_menu'        => $values['content_in_menu'],
                        'content_protected'      => $values['content_protected'],
                        'content_allow_comment'  => $values['content_allow_comment'],
                        'content_layout'         => $values['content_layout'],
                        'content_status'         => $values['content_status'],
                        'content_parent_page'    => $values['content_parent_page'],
                        'content_order'          => $values['content_order'],
                        'content_creator'        => self::$auth->getIdentity()->account_id,
                        'content_create_date'    => time()
                );
                $contentId = self::$contentDb->createContent($data);

                $data = array(
                        'content_id'                           => $contentId,
                        'content_file'                         => $contentFile,
                        'content_description_revision'         => 1,
                        'content_description_active'           => 1,
                        'language'                             => Zend_Registry::get('language'),
                        'content_description_title'            => $values['content_description_title'],
                        'content_description_text'             => $values['content_description_text'],
                        'content_description_meta_title'       => $values['content_description_meta_title'],
                        'content_description_meta_description' => $values['content_description_meta_description'],
                        'content_description_keyword'          => $values['content_description_keyword']
                );
                self::$contentDb->createTranslation($data);

                // redirect to content index
                $this->_redirect('admin/content/index');                
             }
        } else {
            $this->view->form = $form;
        }

        
    }


    /** 
     * @access Public
     * @return void
     */
    public function editAction()
    {        
        
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function deleteAction()
    {
        
        
    }


    /** 
     * @access Public
     * @return void
     */
    public function addTranslationAction()
    {
        
        
    }


    /** 
     * @access Public
     * @return void
     */
    public function editTranslaetionAction()
    {

       
    }


}

