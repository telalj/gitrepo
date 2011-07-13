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
 * @version    $Id: IndexController.php 4 2009-6-1 Jaimie $
 */
class Play_IndexController extends Zend_Controller_Action
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
    private static $filesDb    = null;

    /* @access Public
     * @var object
     */
    private static $accountDb  = null;

    /* @access Public
     * @var object
     */
    public static $auth        = null;

    /* @access Public
     * @var object
     */
    public static $acl         = null;

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
        if ( !$registry->get('moduleConfig')->module->play->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Play');
        }

        self::$artistDb    = new Model_Artist_Db;
    
        self::$albumDb     = new Model_Album_Db;

        self::$filesDb     = new Model_File_Db;

        self::$accountDb   = new Model_Account_Db;

        self::$acl         = $this->view->acl; 
       
        self::$auth        = $this->view->auth;

        self::$config      = $registry->get('moduleConfig')->module->play;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {        
        $tracks = (string)$this->getRequest()->getParam('tracks');

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
            $this->render('access-denied');
        } else {  
   

            // Build SQL list
            $trackArray = explode(':', $tracks);

            $sql = '(';
            for($i = 0; $i < count($trackArray); $i++) {
                if( $trackArray[$i] > 0) {
                    $sql .=  $trackArray[$i];

                    if( (count($trackArray) -2)  > $i) {
                        $sql .= ',';
                    }                
                } 
            }
            $sql .= ')';

            // load tracks
            $fileArray = self::$filesDb->getFilesForPlay($sql);
             

            // Build xspf File
            $url = '';

            /** generate random string for file name
             * we use the account generate code should
             * work fine
             */
            $rand = self::$accountDb->getNewCode(8);

            $xspf = '<?xml version="1.0" encoding="UTF-8"?><playlist version="0" xmlns="http://xspf.org/ns/0/"><trackList>';

            // update counts
            foreach($fileArray as $file) {
                if( $file['file_id'] > 0 ) {

                    self::$filesDb->incrementPlayCount($file['file_id']);

                    self::$albumDb->incrementPlayCount($file['album_id']);
                    self::$artistDb->incrementPlayCount($file['artist_id']);

                    // Update Account play counts
                    $data = array(
                        'date'       => time(),
                        'account_id' => $accountId,
                        'artist'     => $file['artist_id'],
                        'album'      => $file['album_id'],
                        'file_id'    => $file['file_id']
                    );
                    self::$accountDb->setPlayStats($data);

                    /** xspf generation */
                    $url = $this->getRequest()->getBaseUrl()."/play/track/".$file['file_id']."\n";
                    $title = $file['title'];
                    $image = $file['image'];

                    $xspf .="<track><location>{$url}</location><image>{$image}</image><annotation>{$title}</annotation></track>";
                }
            }

            $xspf .="</trackList></playlist>";            

            $filename = Zend_Registry::get('siteRootDir') . "/data/playlist/".$rand.".xspf";
        
            /** Write file */
            $handle = fopen($filename, 'w+');
            fwrite($handle, $xspf);
            fclose($handle);
                 
            $this->view->playlist = $this->getRequest()->getBaseUrl()  ."/data/playlist/{$rand}.xspf";

        }

    } 
}
