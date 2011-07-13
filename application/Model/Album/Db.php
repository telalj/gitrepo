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
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Album_Db
{
    /* @access Public
     * @var object
     */
    private static $db = null;

    /* @access Public
     * @var object
     */
    private static $cache = null;

    /* @access Public
     * @var object
     */
    private static $config = null;


    /**
     * Class constructor
     * @access Public
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        $moduleConfig = Zend_Registry::get('moduleConfig');
        self::$config = $moduleConfig->module->album;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }


    /** 
     * @access Public
     * @param Int $page 
     * @param String $alpha 
     * @return Object
     */
    public function getAllAlbums($page,$alpha)
    {
        /**
         * If we do not have alpha search
         */
        if ( !empty($alpha)) {
            $sql = self::$db->select()
                ->from(array('al' => 'album'), array('*'))
                ->join(array('a' => 'artist'), 'al.artist_id = a.artist_id', array('artist') )
                ->where('al.title LIKE ?', $alpha.'%')
                ->order('al.title')
                ->group('al.title');
        } else {
            $sql = self::$db->select()
                ->from(array('al' => 'album'), array('*'))
                ->join(array('a' => 'artist'), 'al.artist_id = a.artist_id', array('artist') )
                ->order('al.title')
                ->group('al.title');
        }         

         $paginator = Zend_Paginator::factory($sql);

         $paginator->setCurrentPageNumber($page);

         $paginator->setItemCountPerPage(self::$config->perPage);

         $paginator->setPageRange(self::$config->pageRange);
        
        return $paginator;
    }


    /** 
     * @access Public
     * @param String $gui
     * @return Int 
     */
    public function getAlbumIdByGuid($guid)
    {
        $sql = self::$db->select()
                ->from(array('a' => 'album'), array('album_id') )
                ->where('a.album_guid = ?', $guid);

        $result  = self::$db->query($sql)->fetch();
    
        return $result['album_id'];
    }


    /**
     * @access Public
     * @param Int $albumId
     * @return array
     */
    public function getAlbum($albumId)
    {
         $sql = self::$db->select()
            ->from("album")
            ->where('album_id = ?', $albumId);

         $result  = self::$db->query($sql)->fetch();
    
        return $result;
    }


    /** Public function for getting the album count
     * @access Public
     * @return Array 
     */
    public function getAlbumCount()
    {
        $sql = self::$db->select()
                ->from(array('a' => 'album'), array('count(album_id) as album_count') );

        $result  = self::$db->query($sql)->fetch();
    
        return $result['album_count'];
    }    


    /**
     * @access Public
     * @param Int $limit
     * @return Array
     */
    public function getNewAlbums($limit)
    {
        if(!$result = self::$cache->load('getNewAlbums') ) {

            $sql = self::$db->select()
                ->from(array('al' => 'album'), array('album_id', 'title', 'image', 'playtime_secs', 'play_count', 'track_count', 'date_added'))
                ->join(array('ar' => 'artist'), 'al.artist_id = ar.artist_id', array('artist') )
                ->order('al.date_added DESC')
                ->group('al.title')
                ->limit($limit);

            $result  = self::$db->query($sql)->fetchAll();

            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getNewAlbums');
            }  
        }
        
        return $result;
    }

    
    /**
     * @access Public
     * @param Int $albumId
     * @return Void
     */
    public function incrementTrackCount($albumId)
    {
        $sql = self::$db->select()
                ->from(array('a' => 'album'), array('track_count') )
                ->where('a.album_id = ?', $albumId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'track_count' => $result['track_count'] + 1
        );

        self::$db->update('album', $data, 'album_id = ' . $albumId);
    }


    /**
     * @access Public
     * @param Int $albumId
     * @param Int $playTime
     * @return Void
     */
    public function incrementPlayTime($albumId, $playTime)
    {
        $sql = self::$db->select()
                ->from(array('a' => 'album'), array('playtime_secs') )
                ->where('a.album_id = ?', $albumId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'playtime_secs' => $result['playtime_secs'] + $playTime
        );

        self::$db->update('album', $data, 'album_id = ' . $albumId);
    }


    /**
     * @access Public
     * @param Int $albumId
     * @return Void
     */
    public function incrementPlayCount($albumId)
    {
        $sql = self::$db->select() 
            ->from(array('a' => 'album'), array('play_count'))
            ->where('a.album_id = ?', $albumId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'play_count' => $result['play_count'] + 1
        );

        self::$db->update('album', $data, 'album_id = ' . $albumId );
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function newAlbum($data)
    {
        self::$db->insert('album', $data);
        return self::$db->lastInsertId();
    }

}
