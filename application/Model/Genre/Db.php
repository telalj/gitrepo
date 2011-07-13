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
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Genre_Db
{
    /* @access Public
     * @var object
     */
    private static $db      = null;

    /* @access Public
     * @var object
     */
    private static $cache   = null;

    /* @access Public
     * @var object
     */
    private static $config  = null;


    /**
     * Class constructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        $moduleConfig = Zend_Registry::get('moduleConfig');
        self::$config = $moduleConfig->module->artist;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    public function getGenreByParentId($parentId)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url','genre_level', 'genre_name', 'genre_parent_id', 'link'))
            ->where('g.genre_id = ?', $parentId);
           

        $result  = self::$db->query($sql)->fetchAll();

        return $result;
    }    


    public function getChildGenres($genreId)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url','genre_level', 'genre_name', 'genre_parent_id', 'link'))
            ->where('g.genre_parent_id = ?', $genreId);
           

        $result  = self::$db->query($sql)->fetchAll();

        return $result;
    }


    public function getParentGenre($parentId)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url', 'genre_level','genre_name', 'genre_parent_id', 'link'))
            ->where('g.genre_parent_id = ?', $parentId);

        $result  = self::$db->query($sql)->fetchAll();

        return $result;
    }

    
    public function getGenreById($genreId)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url', 'genre_level','genre_name', 'genre_parent_id', 'link'))
            ->where('g.genre_id = ?', $genreId);       

        $result  = self::$db->query($sql)->fetch();

        return $result;

    }


    public function getGenreByName($name)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('g.genre_id', 'g.url', 'g.genre_name', 'g.link'))
            ->where('g.genre_name = ?', $name);

        $result  = self::$db->query($sql)->fetch();


        return $result;
    }

    public function getGenreByUrl($url)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url',  'genre_level','genre_name', 'genre_parent_id', 'link'))
            ->where('g.url = ?', $url);


        $result  = self::$db->query($sql)->fetch();


        return $result;
    }


    public function getGenresByLevel($level)
    {
        $sql = self::$db->select() 
            ->from(array('g' => 'genre'), array('genre_id', 'g.url', 'genre_level','genre_name', 'genre_parent_id', 'link'))
            ->where('g.genre_level = ?', $level);

        $result  = self::$db->query($sql)->fetchAll();

        return $result;

    }


    public function getArtistByGenre($page,$gereId)
    {
        $sql = self::$db->select() 
            ->from(array('ag' => 'artist_genre'), array('artist_genre_id'))
            ->join(array('a' => 'artist'),'ag.artist_id = a.artist_id', array('artist_id', 'artist_guid','artist','image','album_count','track_count','playtime_seconds','play_count','date_added') )
            ->join(array('g' => 'genre'), 'ag.genre_id = g.genre_id', array('genre_id','genre_name', 'url','genre_level','genre_parent_id'))
            ->where('ag.genre_id = ?', $gereId)
            ->group('a.artist');

        $paginator = Zend_Paginator::factory($sql);

         $paginator->setCurrentPageNumber($page);

         $paginator->setItemCountPerPage(self::$config->perPage);

         $paginator->setPageRange(self::$config->pageRange);

        return $paginator;
    }


    public function createGenre($data)
    {
        self::$db->insert('genre', $data);
        return self::$db->lastInsertId(); 
    }

    
    public function mapArtist($data)
    {
        self::$db->insert('artist_genre', $data);
        return self::$db->lastInsertId();
    }
}
