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
 * @version    $Id: RssController.php 4 2009-6-1 Jaimie $
 */
class Album_RssController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $albumRss = null;

    
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
            $this->_redirect('/error/module-not-enabled/module/Album');
        }     

        if(!$registry->get('moduleConfig')->module->album->rss) {
            $this->_redirect('/error/feature-not-enabled');
        }

        self::$albumRss = new Model_Album_Rss;
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
    public function newAlbumsAction()
    {

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);

        // Build Feed Array
        $feedArray = array(
            'title' => 'New Albums',
            'link' => Zend_Registry::get('siteRootUrl'). '/album/rss/new-albums',
            'description' => 'New Albums to VooDoo Music Box',
            'language' => 'en-us',
            'charset' => 'utf-8',
            'pubDate' => date('m-d-Y',time()),
            'generator' => 'VooDoo Music Box',
            'entries' => array()
        );

        /**        
        // Build Feed entries
        foreach ($auctionArray as $r) {
            $feedArray['entries'][] = array(
                'title' => $r['auction_heading'],
                'link' => $this->baseUrl . '/index.php?page=auction:view_item&amp;auction_id='.$r['auction_id'],
                'guid' => $r['auction_id'],
                'description' => 'Current Bid: $' . $r['auction_current_bid_value'] . '. Number of Bids: ' . $r['auction_num_bids'] . '. Qty: ' . $r['auction_item_qty_left']. ' Ends: ' .  date("M-d-Y h:i A ",$r['auction_end_unixtime']). ' PST',
                'pubDate' => date("M-d-Y h:i a",$r['auction_start_unixtime']). ' PST'
                );
        }*/

        // Build Feed
        $feed = Zend_Feed::importArray($feedArray, 'rss');
        echo $feed->send();    

    }
}
