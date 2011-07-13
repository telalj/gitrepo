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
 * @package    File
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_File_Db
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
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        $moduleConfig = Zend_Registry::get('moduleConfig');
        self::$config = $moduleConfig->module->file;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }


    /**
     * @access Public
     * @param String $fileGuid
     * @return Bool
     */
    public function guidExists($fileGuid)
    {
        $sql = self::$db->select()
                ->from(array('f' => 'files'), array('file_guid') )
                ->where('file_guid = ?', $fileGuid);

        $result  = self::$db->query($sql)->fetch();

        if( !empty($result['file_guid']) ) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * @access Public
     * @param String $name
     * @return Array
     */
    public function getFileByName($name)
    {
        $sql = self::$db->select()
            ->from('files')
            ->where('title = ?', $name);

        $result  = self::$db->query($sql)->fetch();

        return $result;
    }
    
    
    /**
     * @access Public
     * @param Int $fileId
     * @return Array
     */
    public function getFile($fileId)
    {
        $sql = self::$db->select()
            ->from('files')
            ->where('file_id = ?', $fileId);

        $result  = self::$db->query($sql)->fetch();

        return $result;
    }


    /**
     * @access Public
     * @param Int $limit
     * @return Array
     */
    public function getTopPlayed($limit)
    {
        if(!$result = self::$cache->load('getTopPlayed') ) {
            $sql = self::$db->select()
                ->from(array('f' => 'files'), array('f.title', 'f.play_count','file_id'))
                ->join(array('ar' => 'artist'), 'f.artist = ar.artist_id', array('artist', 'artist_id'))
                ->join(array('al' => 'album'), 'f.album = al.album_id', array('title as album'))
                ->where('f.play_count > ?', 0)
                ->order('f.play_count DESC')
                ->limit($limit);

            $result  = self::$db->query($sql)->fetchAll();

            /** save cache */
            if(self::$config->cache->enable) {
                self::$cache->save($result, 'getTopPlayed');
            } 
        }

        return $result;
    }


    /**
     * @access Public
     * @param String $sql
     * @return Array
     */
    public function getFilesForPlay($sql)
    {

        $sql = self::$db->select()
            ->from(array('f' => 'files'), array('file_id','filename','title','track') )
            ->join(array('al' => 'album'), 'f.album = al.album_id', array('album_id','image') )
            ->join(array('ar' => 'artist'), 'f.artist = ar.artist_id', array('artist_id') )
            ->where("f.file_id IN $sql" )
            ->order('f.track');

        $result  = self::$db->query($sql)->fetchAll();

        return $result;

    }

    
    /**
     * @access Public
     * @return Array
     */
    public function getFileCount()
    {
         $sql = self::$db->select()
            ->from(array('f' => 'files'), array('count(file_id) as file_count'));
    
        $result  = self::$db->query($sql)->fetch();
        
        return $result['file_count'];
    }


    /**
     * @access Public
     * @param Int $page
     * @oaram Int $parentDir
     * @param String $alpha
     * @return Array
     */
    public function browseMediaDir($page, $parentDir, $alpha)
    {
        if (!empty($alpha)) {
             $sql = self::$db->select()
                ->from(array('md' => 'media_dir'), array('media_dir_id','media_dir_guid','media_dir_name','media_dir_parent','media_dir_path','media_dir_last_scan'))                               
                ->where('media_dir_parent = ?', $parentDir)
                ->where('media_dir_name LIKE ?', $alpha.'%')
                ->order('media_dir_name');
        } else {
            $sql = self::$db->select()
                ->from(array('md' => 'media_dir'), array('media_dir_id','media_dir_guid','media_dir_name','media_dir_parent','media_dir_path','media_dir_last_scan'))
                ->where('media_dir_parent = ?', $parentDir)
                ->order('media_dir_name');
        }


         $paginator = Zend_Paginator::factory($sql);

         $paginator->setCurrentPageNumber($page);

         $paginator->setItemCountPerPage(self::$config->perPage);

         $paginator->setPageRange(self::$config->pageRange);

        return $paginator;
    }


    /**
     * @access Public
     * @return Bool
     */
    public function checkMediaDirHasEntry()
    {
         $sql = self::$db->select()
            ->from(array('md' => 'media_dir'), array('media_dir_id'))
            ->limit(1);

         $result  = self::$db->query($sql)->fetchAll();

        if(count($result) > 0) {
            return true;
        } else {
            return false;
        }
        
    }


    /**
     * @access Public
     * @param String $mediaDirId
     * @return Bool
     */
    public function mediaDirHasChild($mediaDirId)
    {
        $sql = self::$db->select()
            ->from(array('md' => 'media_dir'), array('media_dir_id'))
            ->where('media_dir_parent = ?', $mediaDirId);

        $result  = self::$db->query($sql)->fetchAll();

        if(count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @access Public
     * @param String $dir
     * @return Void
     */
    public function setMediaScanTime($dir)
    {
        $data = array(
            'media_dir_last_scan' => time(),
        );

         self::$db->update('media_dir', $data, "media_dir_guid = '$dir'");        
    }


    /**
     * @access Public
     * @param String $guid
     * @return Array
     */
    public function getMediaDirParent($guid)
    {
        $sql = self::$db->select()
            ->from(array('md' => 'media_dir'), array('media_dir_id','media_dir_guid','media_dir_name','media_dir_parent','media_dir_path'))
            ->where('media_dir_guid = ?', $guid);

        $result  = self::$db->query($sql)->fetch();

        return $result;
    }

    
    /**
     * @access Public
     * @param String $guid
     * @return Bool
     */
    public function checkMediaDir($guid)
    {
         $sql = self::$db->select()
            ->from(array('md' => 'media_dir'), array('media_dir_id'))
            ->where('media_dir_guid = ?', $guid);

        $result  = self::$db->query($sql)->fetch();

        if($result['media_dir_id'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function createMediaDir($data)
    {
        self::$db->insert('media_dir', $data);
        return self::$db->lastInsertId();
    }
    

    /**
     * @access Public
     * @param Int $fileId
     * @return Array
     */
    public function incrementPlayCount($fileId)
    {
        $sql = self::$db->select() 
            ->from(array('f' => 'files'), array('play_count'))
            ->where('f.file_id = ?', $fileId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'play_count' => $result['play_count'] + 1
        );

        self::$db->update('files', $data, 'file_id = ' . $fileId );
    }


    /**
     * @access Public
     * @Param Array $data
     * @return Int
     */
    public function saveFile($data)
    {
        self::$db->insert('files', $data);
        return self::$db->lastInsertId();                                                                
    }

}
