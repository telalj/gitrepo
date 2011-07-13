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
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Artist_Db
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
        self::$config = $moduleConfig->module->artist;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }


    /**
     * @access Public
     * @param Int $page
     * param String $alpha
     * @return Object
     */
    public function getAllArtists($page,$alpha)
    {

        if ( !empty($alpha)) {
            $sql = self::$db->select()
                ->from("artist")
                ->where('artist LIKE ?', $alpha.'%')
                ->order('artist')
                ->group('artist');
        } else {
            $sql = self::$db->select()
                ->from("artist")
                ->order('artist')
                ->group('artist');
        }         

         $paginator = Zend_Paginator::factory($sql);

         $paginator->setCurrentPageNumber($page);

         $paginator->setItemCountPerPage(self::$config->perPage);

         $paginator->setPageRange(self::$config->pageRange);

        return $paginator;
    }


     /**
     * @access Public
     * @return Array
     */
    public function getArtistGenres()
    {
        $sql = self::$db->select()
            ->from(array('a' => 'artist'), array('artist_id', 'artist', 'genres'));

        $result  = self::$db->query($sql)->fetchAll();

        return $result;
    }

    /**
     * @access Public
     * @param String $name
     * @return Array
     */
    public function getArtistByName($name)
    {
        $cacheId = self::_clean($name);

        if(!$result = self::$cache->load('getArtistByName_'.$cacheId) ) {
            $sql = self::$db->select()
                    ->from("artist")
                    ->where('artist = ?', $name);

            $result  = self::$db->query($sql)->fetch();

            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getArtistByName_'.$cacheId);
            }
        }    

        return $result;       
    }

    
    /**
     * @access Public
     * @param Int $artistId
     * @return Array
     */
    public function getArtistByID($artistId)
    {
        $sql = self::$db->select()
                ->from("artist")
                ->where('artist_id = ?', $artistId);

        $result  = self::$db->query($sql)->fetch();
    
        return $result;
    }


    /**
     * @access Public
     * @param String $guid
     * @return Int
     */
    public function getArtistIdByGuid($guid)
    {
        $sql = self::$db->select()
                ->from(array('a' => 'artist'), array('artist_id') )
                ->where('a.artist_guid = ?', $guid);

        $result  = self::$db->query($sql)->fetch();
    
        return $result['artist_id'];
    }


    /**
     * @access Public
     * @param String $guid
     * @return Bool
     */
    public function checkArtistImage($guid){
        $sql = self::$db->select()
                ->from(array('ai' => 'artist_images'), array('id') )
                ->where('ai.guid = ?', $guid);

        $result  = self::$db->query($sql)->fetch();

        if($result['id'] > 0) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @access Public
     * @param Int $artistId
     * @return array
     */
    public function getArtistAlbums($artistId)
    {

        if(!$result = self::$cache->load('getArtistAlbums_'.$artistId) ) {
            $sql = self::$db->select()
                    ->from(array("al" =>"album"), array('album_id','image','released', 'title','track_count', 'playtime_secs', 'play_count', 'date_added'))
                    ->join(array("ar" => "artist"), 'al.artist_id = ar.artist_id', array('artist'))
                    ->where('al.artist_id = ?', $artistId)
                    ->order('al.title')
                    ->group('al.title');

            $result  = self::$db->query($sql)->fetchAll();
            
            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getArtistAlbums_'.$artistId);
            }
        }
        return$result;
    }


    /**
     * @access Public
     * @param Int $artistId
     * @param String $artistAlbum
     * @return Array
     */
    public function getAlbum($artistId, $artistAlbum)
    {
        $cacheId = self::_clean($artistId . $artistAlbum);        

        if(!$result = self::$cache->load('getAlbum_'.$cacheId) ) {

            $sql = self::$db->select()
                    ->from(array("al" =>"album"), array('album_id','image','released', 'title','track_count', 'playtime_secs', 'play_count', 'date_added'))
                    ->join(array("ar" => "artist"), 'al.artist_id = ar.artist_id', array('artist'))
                    ->where('al.artist_id = ?', $artistId)
                    ->where('al.title = ?', $artistAlbum);

            $result  = self::$db->query($sql)->fetch();  
  
            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getAlbum_'.$cacheId);
            }
        }
        
        return $result;
    }
    

    /**
     * @access Public
     * @param Int $page
     * @param Int $albumId
     * @return Object
     */
    public function getAlbumTracks($page,$albumId)
    {
         $sql = self::$db->select()
            ->from("files")
            ->where('album = ?', $albumId)
            ->order('track');       

         $paginator = Zend_Paginator::factory($sql);

         $paginator->setCurrentPageNumber($page);

         $paginator->setItemCountPerPage(self::$config->perPage);

         $paginator->setPageRange(self::$config->pageRange);
        
        return $paginator;
    }


    /**
     * @access Public
     * @param Int $page
     * @param Int $artistId
     * @return Object
     */
    public function getTopPlayedTracks($page,$artistId)
    {
        $sql = self::$db->select()
                ->from(array('f' => 'files'), array('file_id','filename','title','playtime_seconds','play_count'))
                ->join(array('a' => 'album'), 'f.album = a.album_id', array('title as album'))
                ->where('f.artist = ?', $artistId)
                ->order('f.play_count DESC')
                ->order('f.title');

        $paginator = Zend_Paginator::factory($sql);

        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage(self::$config->perPage);

        $paginator->setPageRange(self::$config->pageRange);
        
        return $paginator;
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
                ->from(array('ar' => 'artist'), array('artist', 'image', 'play_count', 'artist_id', 'date_added'))
                ->where('play_count > ?', 0)
                ->order('play_count DESC')
                ->limit($limit);

            $result  = self::$db->query($sql)->fetchAll();       
            
            /** get counts */
            for($i = 0; $i < count($result); $i++) {

                /** album Count */
                $sql = self::$db->select()
                    ->from('album', 'count(album_id) as album_count')
                    ->where('artist_id = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['album_count'] = $rs['album_count'];

                /** track count */
                $sql = self::$db->select()
                    ->from('files', 'count(file_id) as track_count')
                    ->where('artist = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['track_count'] = $rs['track_count'];

                /** Play Time */
                $sql = self::$db->select()
                    ->from('files', 'sum(playtime_seconds) as playtime')
                    ->where('artist = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['playtime'] = $rs['playtime'];            
            }

            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getTopPlayed');
            }
        } 

        return $result;
    }

    
    /**
     * @access Public
     * @param Int $limit
     * @return Array
     */
    public function getNewArtists($limit)
    {
        if(!$result = self::$cache->load('getNewArtists') ) {

            $sql = self::$db->select()
                ->from(array('ar' => 'artist'), array('artist', 'image', 'date_added','play_count', 'artist_id'))
                ->order('date_added DESC')
                ->group('artist')
                ->limit($limit);

            $result  = self::$db->query($sql)->fetchAll();

            /** get counts */
            for($i = 0; $i < count($result); $i++) {

                /** album Count */
                $sql = self::$db->select()
                    ->from('album', 'count(album_id) as album_count')
                    ->where('artist_id = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['album_count'] = $rs['album_count'];

                /** track count */
                $sql = self::$db->select()
                    ->from('files', 'count(file_id) as track_count')
                    ->where('artist = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['track_count'] = $rs['track_count'];

                /** Play Time */
                $sql = self::$db->select()
                    ->from('files', 'sum(playtime_seconds) as playtime')
                    ->where('artist = ?', $result[$i]['artist_id']);
                $rs = self::$db->query($sql)->fetch();
                $result[$i]['playtime'] = $rs['playtime'];            
            }


            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($result, 'getNewArtists');
            }   
      
        }        

        return $result;
    }


    /**
     * @access Public
     * @param Int $page
     * @param Int $artistId
     * @return Object
     */
    public function getArtistImages($page,$artistId)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'artist_images'), array('id','artist_id','title','dateadded','votes','image'))
            ->where('a.artist_id = ?', $artistId);

        $paginator = Zend_Paginator::factory($sql);

        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage(self::$config->perPage);

        $paginator->setPageRange(self::$config->pageRange);
        
        return $paginator;        
    }


    /**
     * @access Public
     * @param Int $imageId
     * @return Array
     */
    public function getArtistImageById($imageId)
    {
        $sql = self::$db->select()
            ->from(array('ai' => 'artist_images'), array('id','guid','artist_id','title','url','dateadded','image'))
            ->where('ai.id = ?', $imageId);

         $result = self::$db->query($sql)->fetch();

        return $result;
    }

    /**
     * @access Public
     * @param Array $simularArtist
     * @return Array
     */
    public function getSimularArtists($simularArtist)
    {
        $cacheId = self::_clean(serialize($simularArtist));

        if(!$array = self::$cache->load('getSimularArtists_'.$cacheId) ) {
        
            $array = array();
            
            $c = 0;

            for($i = 0; $i < count($simularArtist['similar']); $i++) {

                $name = urlencode($simularArtist['similar'][$i]['name']);

                $artist = self::getArtistByName($name);
                
                if( !empty($artist) ) {
                    $array[$c]['artist'] = $artist['artist'];
                    $array[$c]['image']  = $artist['image'];
                    $array[$c]['match']  = $simularArtist['similar'][$i]['match'];

                    $c++;
                } 
            }
  
            /** save cache */
            if(self::$config->cache->enable == 1) {
                self::$cache->save($array, 'getSimularArtists_'.$cacheId);
            }       

        }    

        return $array;
    }

    
    /**
     * @access Public
     * @return Array
     */
    public function getArtistCount()
    {
        $sql = self::$db->select()
            ->from(array('ar' => 'artist'), array('count(artist_id) as artist_count'));
    
        $result  = self::$db->query($sql)->fetch();
        
        return $result['artist_count'];
    }

    
    /**
     * @access Public
     * @param Int $artistId
     * @return Void
     */
    public function incrementAlbumCount($artistId)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'artist'), array('album_count'))
            ->where('artist_id = ?', $artistId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'album_count' => $result['album_count'] + 1
        );
        
        self::$db->update('artist', $data, 'artist_id = ' . $artistId);
        
    }


    /**
     * @access Public
     * @param Int $artistId
     * @return Void
     */
    public function incrementTrackCount($artistId)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'artist'), array('track_count'))
            ->where('artist_id = ?', $artistId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'track_count' => $result['track_count'] + 1
        );
        
        self::$db->update('artist', $data, 'artist_id = ' . $artistId);
    }

    
    /**
     * @access Public
     * @param Int $artistId
     * @return Void
     */
    public function incrementPlayCount($artistId)
    {
        $sql = self::$db->select() 
            ->from(array('a' => 'artist'), array('play_count'))
            ->where('a.artist_id = ?', $artistId);

        $result  = self::$db->query($sql)->fetch();

        $data = array(
            'play_count' => $result['play_count'] + 1
        );

        self::$db->update('artist', $data, 'artist_id = ' . $artistId );
    }
    
    
    /**
     * @access Public
     * @param Array $data
     * @param Int $artistId
     * @return Void
     */
    public function updateArtist($data, $artistId)
    {
         self::$db->update('artist', $data, 'artist_id = ' . $artistId );
    }


    /**
     * @access Public
     * @param Array $data
     * @param Int $imageId
     * @return Void
     */
    public function updateArtistImage($data, $imageId)
    {
        self::$db->update('artist_images', $data, 'id = ' . $imageId );
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function newArtist($data)
    {
        self::$db->insert('artist', $data);
        return self::$db->lastInsertId();                                                                
    }

    
    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function createNewImage($data)
    {
        self::$db->insert('artist_images', $data);
        return self::$db->lastInsertId();   
    }

    
    /**
     * @access Public
     * @param Int $imageId
     * @return Void
     */
    public function removeArtistImage($imageId)
    {
        self::$db->delete('artist_images', 'id = '.$imageId);
    }

    
    /**
     * @access Public
     * @param String $string
     * @return String
     */
    private function _clean($string)
    {
        return md5($string);
    }


}
