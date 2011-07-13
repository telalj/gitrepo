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
 * @package    Account
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: IndexController.php 4 2009-6-1 Jaimie $
 */
class IndexController extends Zend_Controller_Action
{
    
    /* @access Public
     * @var object
     */
    private static $artistDb   = null;

    /* @access Public
     * @var object
     */
    private static $albumDb    = null;
    
    /* @access Public
     * @var object
     */
    private static $fileDb     = null;

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

        self::$artistDb    = new Model_Artist_Db;

        self::$albumDb     = new Model_Album_Db;

        self::$fileDb      = new Model_File_Db;

        self::$acl         = $this->view->acl; 

        self::$auth        = $this->view->auth;       

        self::$config      = $registry->get('moduleConfig')->module->default;
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
            $this->_redirect('error/access-denied/from/default:index/action/view');
        }
        
        /** Enable Dojo */
        $this->view->dojo()->enable()
            ->setDjConfigOption('parseOnLoad', true)
            ->requireModule('dijit.Tooltip');

        $page  = $this->getRequest()->getParam('page');

        $newPage  = $this->getRequest()->getParam('page');

        /** Get Top Played artists */
        $topPlayedArtists = self::$artistDb->getTopPlayed(8);
        $this->view->topPlayedArtists = $topPlayedArtists;
                    
        /** Get New Artists */
        $newArtists = self::$artistDb->getNewArtists(8);
        $this->view->newArtists = $newArtists;

        /** Get New Albums */
        $newAlbums = self::$albumDb->getNewAlbums(8);
        $this->view->newAlbums = $newAlbums;
    }

}
