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
 * @package    Content
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Content_Db
{
    /* @access Public
     * @var object
     */
    private static $db        = null;

    /* @access Public
     * @var object
     */
    private static $cache     = null;

    /* @access Public
     * @var object
     */
    private static $config    = null;

    /* @access Public
     * @var object
     */
    private static $debug     = false;

    /* @access Public
     * @var object
     */
    private static $cacheName = '';
    
    
    /**
     * Class constructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        self::$config   = new Zend_Config_Ini(Zend_Registry::get('siteRootDir') . '/application/Configs/Content.ini', 'default');
        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }



    /**
     * @access Public
     * @param String $string
     * @return String
     */
    public function cacheName($string)
    {
        $find = array("/[^a-zA-Z0-9\s]/","/\s+/");
        $replace = array(" ","");
        $string = strtolower(preg_replace($find,$replace,$string));       

        return $string;
    }


    /**
     * @access Public
     * @param Int $page
     * @return Object
     */
    public function getPages($page)
    {
        $selection = self::$db->select()->from("content");
        
        $paginator = Zend_Paginator::factory($selection);

        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage(5);

        $paginator->setPageRange(5);

        return $paginator;
    }

    
    /**
     * @access Public
     * @return Array
     */
    public function getParentPageNames()
    {

        if (!($results = self::$cache->load('getParentPageNames'))) {

            $sql = self::$db->select()
                ->from(array('c' => 'content'), array('content_id', 'content_page') )
                ->where('content_status = ?', 'published');

            $result = self::$db->query($sql)->fetchAll();
        
            self::$cache->save($results);
        }

        return $result;                 
    }

    
    /** 
     * @access Public
     * @param String $contentPage
     * @return Array
     */
    public function getContentIdByPageName($contentPage)
    {

         if (!($results = self::$cache->load( $this->cacheName('getContentIdByPageName_'.$contentPage)  ))) {

             $sql = self::$db->select()
                ->from(array('c' => 'content'), array('content_id') )
                ->where('content_page = ?', $contentPage);             

            $result = self::$db->query($sql)->fetch();

            self::$cache->save($results);
        }

        return $result;
    }


    /**
     * @access Public
     * @return
     */
    public function getPublishedPage($contentPage, $language)
    {
        if (!($results = self::$cache->load($this->cacheName('getPublishedPage_'.$contentPage.'_'.$language) ))) {

            $sql = self::$db->select()
                ->from( array('c' => 'content'), array('content_id', 'content_page', 'content_build_time','content_build_time',
                            'content_protected','content_allow_comment','content_parent_page','content_layout','content_order',
                            'content_creator','content_create_date') )
                ->join( array('cd' => 'content_description'), 'c.content_id = cd.content_id', array('content_file', 'content_description_revision',
                            'content_description_title', 'content_description_meta_title','content_description_meta_description','content_description_keyword' ) )
                ->where('c.content_page = ? ', $contentPage)
                ->where('cd.language = ? ', $language)
                ->where('c.content_status = ?', 'published');

            $result = self::$db->query($sql)->fetch();
             
            self::$cache->save($results);
        }

        return $result;       
    }


    /** 
     * @access Public
     * @return Array
     */
    public function getMenuPages()
    {
       if (!($results = self::$cache->load('getMenuPages'))) { 

            $sql = self::$db->select()
                ->from( array('c' => 'content'), array('content_page', 'content_id'))
                ->join(array('cd' => 'content_description'), 'c.content_id = cd.content_id', array('content_file', 'content_description_title'))
                ->where('c.content_in_menu = ?', '1')
                ->where('c.content_status = ? ', 'published')
                ->order(array(' c.content_order DESC', 'cd.content_description_title'));

            $result = self::$db->query($sql)->fetchAll();

            self::$cache->save($results);
        }

        return $result;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function createContent($data)
    {
        self::$db->insert('content', $data);
        return self::$db->lastInsertId();
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function createTranslation($data)
    {
        self::$db->insert('content_description', $data);
        return self::$db->lastInsertId();
    }

}
