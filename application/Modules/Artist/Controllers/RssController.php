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
 * @package    Artist
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: RssController.php 4 2009-6-1 Jaimie $
 */
class Artist_RssController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $artistRss = null;

    
    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        // load registry
        $registry = Zend_Registry::getInstance();
        
        // if module is active
        if ( !$registry->get('moduleConfig')->module->artist->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Artist');
        }     

        if(!$registry->get('moduleConfig')->module->artist->rss) {
            $this->_redirect('/error/feature-not-enabled');
        }

        self::$artistRss = new Model_Artist_Rss;
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {        
          
    }


    /** 
     * @access Public
     * @return void
     */
    public function newArtistsAction()
    {

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        $page = $this->getRequest()->getParam($page);
        
        // Build Feed Array
        $feedArray = array(
            'title' => 'New Artists',
            'link' => Zend_Registry::get('siteRootUrl'). '/artist/rss/new-artists',
            'description' => 'New Artists to VooDoo Music Box',
            'language' => 'en-us',
            'charset' => 'utf-8',
            'pubDate' => date('m-d-Y',time()),
            'generator' => 'VooDoo Music Box',
            'entries' => array()
        );

        

        // Build Feed
        $feed = Zend_Feed::importArray($feedArray, 'rss');
        echo $feed->send();    
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function artistAlbumsAction()
    {
        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        // Build Feed Array
        $feedArray = array(
            'title' => 'Artist Albums',
            'link' => Zend_Registry::get('siteRootUrl'). '/artist/rss/artist-albums',
            'description' => 'Artist Albums on VooDoo Music Box',
            'language' => 'en-us',
            'charset' => 'utf-8',
            'pubDate' => date('m-d-Y',time()),
            'generator' => 'VooDoo Music Box',
            'entries' => array()
        );

       

        // Build Feed
        $feed = Zend_Feed::importArray($feedArray, 'rss');
        echo $feed->send();
    }


    /** 
     * @access Public
     * @return void
     */
    public function artistAction()
    {
        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        $page = $this->getRequest()->getParam('page');

        // Build Feed Array
        $feedArray = array(
            'title' => 'All Artists',
            'link' => Zend_Registry::get('siteRootUrl'). '/artist/rss/artists',
            'description' => 'Artists on VooDoo Music Box',
            'language' => 'en-us',
            'charset' => 'utf-8',
            'pubDate' => date('m-d-Y',time()),
            'generator' => 'VooDoo Music Box',
            'entries' => array()
        );

        $artistArray = self::$artistRss->getAllArtists($page);

               
        // Build Feed entries
        foreach ($artistArray as $r) {
            $feedArray['entries'][] = array(
                'title' => $r['artist'],
                'link' =>   Zend_Registry::get('siteRootUrl').'/artist/view/id/'.$r['artist_id'],
                'guid' => $r['artist_id'],
                'description' => '<img src="'.Zend_Registry::get('siteRootUrl').$r['image'].'" align="left" hspace="5">' . $r['description'],
                
                'pubDate' => date("M-d-Y h:i a",$r['date_added']). ' PST'
                );
        }


        
    
        // Build Feed
        $feed = Zend_Feed::importArray($feedArray, 'rss');
        echo $feed->send();
    }
    

}
