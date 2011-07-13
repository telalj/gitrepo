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
 * @package    Default
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: BoxController.php 4 2009-6-1 Jaimie $
 */
class BoxController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $boxDb        = null;

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
    private static $genreDb   = null;

    /* @access Private
     * @var object
     */
    private static $acl          = null;

    /* @access Public
     * @var object
     */
    private static $themeConfig  = null;

    /* @access Public
     * @var object
     */
    private static $siteRootDir  = null;

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
        self::$boxDb = new Model_Box_Db;

        // add view path
        self::$themeConfig =  Zend_Registry::get('theme');

        self::$siteRootDir = Zend_Registry::get('siteRootDir');

        $theme = self::$themeConfig->directory;

        $this->view->addScriptPath(self::$siteRootDir . '/themes/'.$theme.'/default/scripts'); 

        self::$moduleConfig = Zend_Registry::get('moduleConfig');

        self::$acl         = $this->view->acl; 

        self::$auth        = $this->view->auth;

    }


    /** 
     * @access Public
     * @return void
     */
    public function leftAction()
    {
        $layout = Zend_Registry::get('layout');

        $boxes = self::$boxDb->getBoxLayout($layout, 'left');

        $this->view->leftBoxes = $boxes;
    }


    /** 
     * @access Public
     * @return void
     */
    public function rightAction()
    {
        $layout = Zend_Registry::get('layout');

        $boxes = self::$boxDb->getBoxLayout($layout, 'right');

        $this->view->rightBoxes = $boxes;
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function topPlayedAction()
    {
        $fileDb = new Model_File_Db;

        $topPlayed = $fileDb->getTopPlayed(40);
        
        $this->view->topPlayed = $topPlayed;
    }    


    /** 
     * @access Public
     * @return void
     */
    public function artistAction()
    {
        /** Acls */
        if(self::$auth->hasIdentity()) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }
        
        $this->view->accountType = $accountType;  

        $artistDb = new Model_Artist_Db;

        $this->view->moduleConfig = self::$moduleConfig->module->artist;

        $artistId   = $this->getRequest()->getParam('artist_id');
        $artistName = $this->getRequest()->getParam('name');
        $albumName  = $this->getRequest()->getParam('album');
        $trackName  = $this->getrequest()->getParam('track');
        
        /** get albums By Artist */
        if( !empty($artistId) ){
            $this->view->albums = $artistDb->getArtistAlbums($artistId);
        }

        $this->view->artistName = $artistName;
        $this->view->trackName  = $trackName;
        $this->view->albumName  = $albumName;
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function albumAction()
    {
        $this->view->moduleConfig = self::$moduleConfig->module->album;

    }


    /** 
     * @access Public
     * @return void
     */
    public function genreAction()
    {
        self::$genreDb = new Model_Genre_Db;

        $this->view->moduleConfig = self::$moduleConfig->module->genre;

        $parentId = (int)$this->getRequest()->getParam('parentId');

        $genre = self::$genreDb->getGenreById($parentId);
    
        $this->view->genre = $genre;

        $parentId = $genre['genre_parent_id'];
        $level    = $genre['genre_level'] - 1;
        
        $parentGenre[$genre['genre_level']] = $genre;

        while( $level > 0 ) {
            // get parent
            $parentGenre[$level] = self::$genreDb->getGenreById($parentId);

            $parentId = $parentGenre[$level]['genre_parent_id'];        
            
            $level--;
        }
         
        $parentGenre = array_reverse($parentGenre);


        // get top level genres
        $topArray = self::$genreDb->getChildGenres(0);

        for($i = 0; $i < count($topArray); $i++) {
            
            foreach($parentGenre as $parent) {
                // level2
                if( $topArray[$i]['genre_id'] == $parent['genre_id']) {

                    // get childs
                    $level2 = self::$genreDb->getChildGenres($parent['genre_id']);
                    $topArray[$i]['child'] = $level2;
                    
                    // level3
                    for($ii = 0; $ii < count($topArray[$i]['child']); $ii++) {

                        foreach($parentGenre as $parent) { 
                           
                            if ( $topArray[$i]['child'][$ii]['genre_id'] == $parent['genre_id']) {
                                $level3 = self::$genreDb->getChildGenres($parent['genre_id']);
                                $topArray[$i]['child'][$ii]['child'] = $level3;

                                // level4
                                for($iii = 0; $iii < count($topArray[$i]['child'][$ii]['child']); $iii++ ){

                                     foreach($parentGenre as $parent) {

                                        if($topArray[$i]['child'][$ii]['child'][$iii]['genre_id'] == $parent['genre_id']) {
                                            $level4 = self::$genreDb->getChildGenres($parent['genre_id']);
                                            $topArray[$i]['child'][$ii]['child'][$iii]['child'] = $level4;
                                        } // end if

                                    } // end foreach

                                } //end for

                            }// end level 4 
                        }

                    }// end level 3
                   
                } // end level 2

            } // end foreach loop

        } // end for
       

        $this->view->genreArray = $topArray;
    }


    /** 
     * @access Public
     * @return void
     */
    public function contentAction()
    {
        $contentDb = new Model_Content_Db;

        $menuPageArray = $contentDb->getMenuPages();

        $this->view->menuPageArray = $menuPageArray;
    }


    /** 
     * @access Public
     * @return void
     */
    public function languageAction()
    {
        $languageForm = new Model_Language_Form;
        
        $requestFrom  = $this->getRequest()->getRequestUri();

        $this->view->langForm = $languageForm->getLanguageSwitchForm($requestFrom);

        
    }

}
