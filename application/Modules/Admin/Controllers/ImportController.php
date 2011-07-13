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
 * @version    $Id: CountryController.php 4 2009-6-1 Jaimie $
 */
class Admin_ImportController extends Zend_Controller_Action
{
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

    /* @access Public
     * @var object
     */
    private static $artistDb    = null;

    /* @access Public
     * @var object
     */
    private static $albumDb     = null;

    /* @access Public
     * @var object
     */
    private static $genreDb     = null;

    /* @access Public
     * @var object
     */
    private static $fileDb      = null;

    /* @access Public
     * @var object
     */
    private static $rootPath    = null;

    /* @access Public
     * @var object
     */
    private static $moduleForm  = null;

    /* @access Public
     * @var object
     */
    private static $moduleConfig = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        error_reporting(E_ALL ^ E_NOTICE);
		ini_set('display_errors', true);

        // load registry
        $registry = Zend_Registry::getInstance();

        self::$moduleConfig = $registry->get('moduleConfig')->module->admin;
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
        
        foreach (self::$moduleConfig->acl as $key => $val) {
            $resourceArray = explode(':', $val);
            self::$acl->allow($key,  null,   $resourceArray);            
        }    

        if(!self::$acl->isAllowed($accountType, null, 'admin') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/admin:import');
        }

        self::$config   = Zend_Registry::get('configuration');

        self::$artistDb = new Model_Artist_Db;
    
        self::$albumDb  = new Model_Album_Db;

        self::$genreDb  = new Model_Genre_Db;

        self::$fileDb   = new Model_File_Db;   

        self::$rootPath = Zend_Registry::get('siteRootDir'); 

        self::$moduleForm = new Model_Module_Form;

    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        

        $page     = $this->getRequest()->getParam('page');
        $parentId = $this->getRequest()->getParam('parentId');
        $alpha    = $this->getRequest()->getParam('alpha');

        // do check to see if we even have a dir to scan if not redirect to the import media dir
        if(!self::$fileDb->checkMediaDirHasEntry()) {
            $this->_redirect('admin/import/directory');
        }

        if(!isset($parentId)) {
            $parentId = 0;   
        }

        // build letter box
        $letter_box='<a href="admin/import/index">All&nbsp;&nbsp;&nbsp;</a>';

        if ($alpha == 'All' ) {
            $alpha = '';
        }

        for ( $letter=1; $letter<=26; $letter++ ) {         
            if ( $letter<= 26 ){
                $inside  = chr($letter+64);
            } 
        
            $letter_box .=  '<a href="admin/import/index/parentId/'.$parentId.'/alpha/'.$inside.'">'.$inside.'</a>&nbsp;&nbsp;&nbsp;';
        }
        $this->view->letterSearch = $letter_box;

        $folderArray = self::$fileDb->browseMediaDir($page, $parentId, $alpha);
        $this->view->folderArray = $folderArray;        
            
        
       

        $form = self::$moduleForm->importForm($folderArray,$parentId ,$alpha, $page);
    
        

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $sessionNamespace = new Zend_Session_Namespace('File_Import');

                $sessionNamespace->data = $values;

                $this->view->iframe = true;                
            }
        } else {
            $this->view->form = $form;
        }
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function processAction()
    {
        set_time_limit (0); 

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        $scan = new Model_Module_Scan;

        $sessionNamespace = new Zend_Session_Namespace('File_Import');
    
        $filter    = self::$config->media->fileExt;

         echo '<script type="text/javascript">
                <!--
                function pageScroll() {

                window.scrollBy(0,25);
                scrolldelay = setTimeout(\'pageScroll()\',200); //Increase this # to slow down, decrease to speed up scrolling

                }

                pageScroll();
                //-->
                </script>';

        echo "Please wait while we scan your directory for music<br>";
        echo '<table width="100%">';

        foreach($sessionNamespace->data as $key => $val) {
        	
            if(!empty($val) && is_readable($val)) {
                $scan->scan_directory_recursively($val, $filter);
                
            }
        }

        
        echo '</table>';
        echo 'Scan complete';
    
    }


    /** 
     * @access Public
     * @return void
     */
    public function genreAction()
    {
        $form = self::$moduleForm->importGenreForm();

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $this->view->iframe = true;
            }
        } else {
            $this->view->form = $form;
        }
    }  

    
    /** 
     * @access Public
     * @return void
     */
    public function processGenreAction()
    {
        
        set_time_limit (0); 

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        /** Get all artists */
        $artistArray = self::$artistDb->getArtistGenres();
        echo '<table "100%">';

        
        foreach($artistArray as $artist) 
        {
            $artistGenre = array();
    
            echo "<tr><td>Working on {$artist['artist']}</td>";
            
            $genreArray = unserialize($artist['genres']);

            if( !empty($genreArray) ) 
            {
                $i = 0;

                foreach($genreArray['tags'] as $genre) 
                {
                    if(!empty($genre['name'])) 
                    {
                        /** Get genre from DB */
                        $newGenre = self::$genreDb->getGenreByName($genre['name']);
                    
                        $tempGenre = array();

                        if(!empty($newGenre['genre_id'])) 
                        {
                            $tempGenre['genre_id']  = $newGenre['genre_id'];
                            $tempGenre['artist_id'] = $artist['artist_id'];
            
                            $artistGenre[$i]['genre_name'] = $genre['genre_name'];
                            $artistGenre[$i]['genre_id']   = $genre['genre_id'];
                                            
                            /** Map genre to artist */
                            self::$genreDb->mapArtist($tempGenre);
                            $i++;                    
                        }
                        
                    }                    
                }

                /** Set artist new genre */
                $data = array(
                    'genres' => serialize($artistGenre)
                );
                self::$artistDb->updateArtist($data , $artist['artist_id']);
                echo '<td width="10">[<span style="color:green">OK</span>]</td></tr>';

            }
        }

        echo '</table>';
    } 
    

    /** 
     * @access Public
     * @return void
     */
    public function directoryAction()
    {
        $form = self::$moduleForm->importDirectoryForm();

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $this->view->iframe = true;
        
            }
        } else {
            $this->view->form = $form;
        }

    }

    
    /** 
     * @access Public
     * @return void
     */
    public function processDirectoryAction()
    {
        set_time_limit (0); 

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);
        
        $config   = Zend_Registry::get('configuration');

        $rootPath = Zend_Registry::get('siteRootDir');

        $path = $rootPath . $config->media->path;

        // check parent path
        $pathGuid  = md5($path);

        if(!self::$fileDb->guidExists($pathGuid)) {
            // create first Dir
            $subdirectories = explode('/',$path);
            $dirName        = end($subdirectories);
            
                    
            $data = array (
                'media_dir_guid'   => $pathGuid,
                'media_dir_name'   => $dirName,
                'media_dir_parent' => 0,
                'media_dir_path'   => $path,     
            );

            self::$fileDb->createMediaDir($data);
        }


        echo '<p>Begin scan of ' . $path . '</p>'; 
        
        $scan = new Model_Module_Scan;

        $scan->importDirectoryStructure($path);

        echo '<p>Done scanning directory.</p>';

    }


    /** 
     * @access Public
     * @return void
     */
    public function uploadAction()
    {
        $form = self::$moduleForm->importUploadForm();

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                

                $this->view->iframe = true;
            }
        } else {
            $this->view->form = $form;
        }
    }
}

