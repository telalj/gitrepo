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
 * @package    Album
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: EditController.php 4 2009-6-1 Jaimie $
 */
class Album_EditController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $albumDb   = null;

    /* @access Public
     * @var object
     */
    private static $albumForm = null;

    /* @access Public
     * @var object
     */
    private static $albumMail = null;
 
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
        if ( !$registry->get('moduleConfig')->module->album->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Artist');
        }

        self::$albumDb    = new Model_Album_Db;
    
        self::$albumForm  = new Model_Album_Form;

        self::$albumMail  = new Model_Album_Mail;

        self::$acl         = $this->view->acl; 

        self::$auth        = $this->view->auth;

        self::$config      = $registry->get('moduleConfig')->module->album;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        $albumName = $this->getRequest()->getParam('name');

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
            $this->_redirect('error/access-denied/album:edit:name:'.urlencode($albumName));
        }


        
    }

}
