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
 * @package    Play
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: TrackController.php 4 2009-6-1 Jaimie $
 */
class Play_TrackController extends Zend_Controller_Action
{

    /* @access Public
     * @var object
     */
    private static $filesDb = null;

    /* @access Public
     * @var object
     */
    public static $auth    = null;

    /* @access Public
     * @var object
     */
    public static $acl     = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    { 
        // load registry
        $registry = Zend_Registry::getInstance();
        
        // if module is active
        if ( !$registry->get('moduleConfig')->module->play->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Play');
        }
        
        /** Disable the view */
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        self::$acl      = $this->view->acl; 
       
        self::$auth     = $this->view->auth;

        self::$filesDb  = new Model_File_Db;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {  

        if( self::$auth->hasIdentity() ) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }

        /** @todo ACL check */


        $fileId = (int)$this->getRequest()->getParam('id');
    
        /** if we have a file id */
        if( $fileId > 0 ) {
            $file = self::$filesDb->getFile($fileId);

            $filePath = $file['filename'];
            $tmp      = explode( "/", $filePath );
            $filename = end( $tmp );
        
            if( file_exists($filePath) ){          
                // file to user
                header('Cache-Control: public'); // needed for i.e.
                header('Content-Type: audio/mpeg');
                header('Content-Disposition: attachment; filename="'.$filename.'"');
                readfile($filePath);
                die(); // stop execution other wise server will loop forever
           
            } else {
                throw new exception('Missing file: ' . $filePath);
            }   
        }
    }

}
