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
 * @package    Genre
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: IndexController.php 4 2009-6-1 Jaimie $
 */
class Genre_IndexController extends Zend_Controller_Action
{

    /* @access Public
     * @var object
     */
    private static $genreDb   = null;

    /* @access Public
     * @var object
     */
    private static $genreForm = null;

    /* @access Public
     * @var object
     */
    private static $genreMail = null;

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
        if ( !$registry->get('moduleConfig')->module->genre->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Genre');
        }


        self::$genreDb    = new Model_Genre_Db;
    
        self::$genreForm  = new Model_Genre_Form;

        self::$genreMail  = new Model_Genre_Mail;

        self::$acl         = $this->view->acl; 

        self::$auth        = $this->view->auth;

        self::$config      = $registry->get('moduleConfig')->module->genre;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {        
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

        if(!self::$acl->isAllowed($accountType, null, 'view') ? "1" : "0") {
            $this->_redirect('error/access-denied');
        }
        
        $page  = (int)$this->getRequest()->getParam('page');

        $name = (string)$this->getRequest()->getParam('name');

        $genre = self::$genreDb->getGenreByUrl($name);

        $this->view->genre = $genre;        

        // load artists by genre
        if ( $genre['genre_id'] < 1 ) {
            $genId = '*';
        } else {
             $genId = $genre['genre_id'];
        }
        $artistArray = self::$genreDb->getArtistByGenre($page, $genId);
        $this->view->artistArray = $artistArray;

        // breadcrumb
        $parentId = $genre['genre_parent_id'];
        $level    = $genre['genre_level'] - 1;
        $parentGenre = array();
        $parentGenre[$genre['genre_level']] = $genre;
        while( $level > 0 ) {
            // get parent
            $parentGenre[$level] = self::$genreDb->getGenreById( $parentId);

            $parentId = $parentGenre[$level]['genre_parent_id'];        
            
            $level--;
        }
         
        if(!empty($parentGenre)) {
            $parentGenre = array_reverse($parentGenre);
        } else {
            $parentGenre[0] = $genre;
        }
        
        
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Home'), 'url' => $this->view->baseUrl);
        $breadcrumbs[] = array('title' => $this->view->translate('Menu_Genre'), 'url' => 'genre/');

        foreach($parentGenre as $genre) {
             $breadcrumbs[] = array('title' => $genre['genre_name'], 'url' => 'genre/' . urlencode($genre['url']));
        }

       
        $this->view->placeholder('breadcrumbs')->exchangeArray( Helper_Breadcrumb::process($breadcrumbs) );

        // meta tags
    }
}
