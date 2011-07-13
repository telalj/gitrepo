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
 * @package    Module
 * @subpackage Scan
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Scan.php 4 2009-6-1 Jaimie $
 */
class Model_Module_Scan 
{

    /* @access Public
     * @var object
     */
    private static $artistDb    = null;

    /* @access Public
     * @var object
     */
    private static $albumDb     = null;

    /* @access Public
     * @var object
     */
    private static $fileDb      = null;

    /* @access Public
     * @var object
     */
    private static $genreDb    = null;

    /* @access Public
     * @var object
     */
    private static $config      = null; 

    /* @access Public
     * @var object
     */
    private static $count       = null;  

 
    /** Contructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$artistDb = new Model_Artist_Db;

        self::$albumDb  = new Model_Album_Db;
        
        self::$fileDb   = new Model_File_Db;

        self::$genreDb  = new Model_Genre_Db;

        self::$config   = Zend_Registry::get('configuration');
    }


    /**
     * @access Public
     * @param String $directory
     * @return Bool
     */
    public function importDirectoryStructure($directory)
    {
        

        // if the path has a slash at the end we remove it here
        if(substr($directory,-1) == '/'){
            $directory = substr($directory,0,-1);
        }
	    $dirCount =count( explode("/", $directory) );

        // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory)){
        // ... we return false and exit the function
        return FALSE;
        // ... else if the path is readable
    } elseif (is_readable($directory)){
        // we open the directoryi
        $directory_list = opendir($directory);

        

        // and scan through the items inside
        while (FALSE !== ($file = readdir($directory_list))){
            // if the filepointer is not the current directory
            // or the parent directory
            if($file != '.' && $file != '..'){
                // we build the new path to scan
                $path = $directory.'/'.$file;
                // if the path is readable
                if(is_readable($path)){
                    // we split the new path by directories
                    $subdirectories = explode('/',$path);
  
                    // if the new path is a directory
                    if(is_dir($path)) {
                        // add the directory details to the file list
                        
                        $dirName =  end($subdirectories);
                        
                        echo 'This Folder: ' . $dirName .'<br>';
                        echo 'This Path: ' . $path . '<br>';
                        $pathGuid = md5($path);

                        // parent Path
                        array_pop($subdirectories);
                        $parentPath = implode('/', $subdirectories);
                        echo 'This Parent Path: ' . $parentPath . '<br>';
                        $parentGuid = md5($parentPath);
                        
                        // get parent id
                        $parent = self::$fileDb->getMediaDirParent($parentGuid);
                        if( empty($parent['media_dir_id'])) {
                            $parent['media_dir_id'] = 0;
                        }

                        // create this dir
                        if(!self::$fileDb->checkMediaDir($pathGuid)) {
                            $data = array (
                                'media_dir_guid'   => $pathGuid,
                                'media_dir_name'   => $dirName,
                                'media_dir_parent' => $parent['media_dir_id'],
                                'media_dir_path'   => $path,     
                            );

                            self::$fileDb->createMediaDir($data);
    
                        }

                        echo '<br>';
      
                    
                        $dirGuid = md5($path);
                        //print_r($path);
                      
                        self::importDirectoryStructure($path);
                                             
                    } // end is file
                } // end if readable
            } // end if . or ..
        } // End While Loop
        
        // close the directory
        closedir($directory_list); 

        // if the path is not readable ...
        }else{
            // ... we return false
            return FALSE;    
        }
    }


    /**
     * @access Public
     * @param String $directory
     * @param Bool $filter
     * @return Bool
     */
    public function scan_directory_recursively($directory, $filter=FALSE)
    {

    // if the path has a slash at the end we remove it here
    if(substr($directory,-1) == '/'){
        $directory = substr($directory,0,-1);
    }
	$dirCount =count( explode("/", $directory) );

    

    // if the path is not valid or is not a directory ...
    if(!file_exists($directory) || !is_dir($directory)){
        // ... we return false and exit the function
        return FALSE;
        // ... else if the path is readable
    } elseif (is_readable($directory)){
        // we open the directoryi
        $directory_list = opendir($directory);
        
        // and scan through the items inside
        while (FALSE !== ($file = readdir($directory_list))){
            // if the filepointer is not the current directory
            // or the parent directory
            if($file != '.' && $file != '..'){
                // update timestamp for dir
                self::$fileDb->setMediaScanTime(md5($directory));

                // we build the new path to scan
                $path = $directory.'/'.$file;
            
                // if the path is readable
                if(is_readable($path)){
                    // we split the new path by directories
                    $subdirectories = explode('/',$path);
  
                    // if the new path is a directory
                    if(is_dir($path)) {
                        
                        // we scan the new path by calling this function
                        self::scan_directory_recursively($path, $filter);
        
                    
                    // if the new path is a file work on the file
                    } elseif (is_file($path)){
                        // get the file extension by taking everything after 
                        //the last dot
                        $extension = end(explode('.',end($subdirectories)));

                        // if there is no filter set or the filter is set 
                        // and matches
                        if($filter === FALSE || $filter == $extension){                    
                    
                            // Create GUID using MD5 of the file                    
                            $fileGuid = trim(md5_file($path));
                        
                            echo '<tr>';
                            // check if we have file already
                            if(self::$fileDb->guidExists($fileGuid)) {
                                echo '<td>Working on file: ' . $fileGuid . '</td>';

                                // get MP3 ID
                                if($tag = self::_getID3Tag($path)) {
    
                                    $pathInfo = pathinfo($path);
                                    
                                    $pathArray = explode('/', $pathInfo['dirname']);     

                                    // album name
                                    $albumPath ='';
                                    foreach($pathArray as $alPath) {
                                        $albumPath .= $alPath . '/';
                                    }                                    
                                    $albumGuid = md5($albumPath);

                                    // artist name
                                    array_pop($pathArray);
                                    $artistPath ='';
                                    foreach($pathArray as $arPath) {
                                        $artistPath .= $arPath . '/';
                                    } 
                                    $artistGuid = md5($artistPath);

                                    // get artist   
                                    $artistId = self::_getArtistId($artistGuid, $tag['artistName'], $artistPath);

                                    // get album
                                    $albumId  = self::_getAlbumId($artistId, $tag['artistName'], $albumGuid, $tag['albumName'], $albumPath);

                                    // build file array
                                    $data = array(
                                        'file_guid'         => $fileGuid,
                                        'filename'          => $path,
                                        'artist'            => $artistId,
                                        'album'             => $albumId,
                                        'title'             => self::_stripSlash($tag['title']),
                                        'genre'             => $tag['genre'],
                                        'track'             => $tag['track'],
                                        'year'              => $tag['year'],
                                        'composer'          => $tag['composer'],
                                        'publisher'         => $tag['publisher'],
                                        'provider'          => $tag['provider'],
                                        'playtime_seconds'  => $tag['playTime'],
                                        'bitrate'           => $tag['bitRate'],
                                        'filesize'          => $tag['filesize'],
                                        'fileformat'        => $tag['fileformat'],
                                        'codec'             => $tag['codec'],
                                        'sample_rate'       => $tag['sampleRate'],
                                        'dataformat'        => $tag['dataFormat'],
                                        'mime_type'         => $tag['mimeType'],
                                        'id3'               => 1
                                    );
                    
                                    $playTime = $tag['playTime'];

                                    // no tag use file name
                                } else {
                                    
                                    $pathInfo = pathinfo($path);
                                    
                                    // title
                                    $title = self::_cleanText($pathInfo['filename']);
                                    
                                    $pathArray = explode('/', $pathInfo['dirname']);     

                                    // album name
                                    $albumName = self::_cleanText(end($pathArray));
                                    $albumPath ='';
                                    foreach($pathArray as $alPath) {
                                        $albumPath .= $alPath . '/';
                                    }                                    
                                    $albumGuid = md5($albumPath);

                                    // artist name
                                    array_pop($pathArray);
                                    $artistName = self::_cleanText(end($pathArray));
                                    $artistPath ='';
                                    foreach($pathArray as $arPath) {
                                        $artistPath .= $arPath . '/';
                                    } 
                                    $artistGuid = md5($artistPath);
                                
                                    // get artist
                                    $artistId = self::_getArtistId($artistGuid, $artistName, $artistPath);

                                    // get album
                                    $albumId  = self::_getAlbumId($artistId, $artistName,  $albumGuid, $albumName, $albumPath);

                                    $data = array(
                                        'file_guid'         => $fileGuid,
                                        'filename'          => $path,
                                        'artist'            => $artistId,
                                        'album'             => $albumId,
                                        'title'             => self::_stripSlash($title),
                                        'fileformat'        => $titleArray[1],
                                        'filesize'          => filesize($path),
                                        'id3'               => 0
                                    );
                                    
                                    $playTime = 0;
                                }

                                if (!empty($data)) {
                                    self::$fileDb->saveFile($data); 
            
                                    // update counts
                                    self::_updateCounts($playTime, $artistId, $albumId );
        
                                    self::$count++;
                                    
                                    
                                    echo '<td width="10">[<span style="color:green">OK</span>]</td>';
                                } else {
                                    echo '<td width="10">[<span style="color:red">Fail</span>]</td>';
                                }
                            } else {
                                echo '<td>Skiping file: ' . $fileGuid . '</td>';
                                echo '<td width="10">[<span style="color:yellow">SKIP</span>]</td>';
                            } 

                            echo '</tr>';
                                                     
                        } // end fileter check
                        
                    } // end is file
                } // end if readable
            } // end if . or ..
        } // End While Loop
        
        // close the directory
        closedir($directory_list); 


        // if the path is not readable ...
        }else{
            // ... we return false
            return FALSE;    
        }
    } // end scan dir function


    /**
     * @access Public
     * @param String $path
     * @return String
     */
    private function _getID3Tag($path)
    {
        require_once (Zend_Registry::get('siteRootDir') .'/library/getid3/getid3.php');

        $getID3   = new getID3;
        $fileInfo = $getID3->analyze($path);
        $id3      = $fileInfo['id3v1'];
        
        $tag = array();        

        if ( !empty( $id3['title'] ) ) {          
            $tag['title']      = htmlspecialchars($id3['title'], ENT_QUOTES);    
            $tag['artistName'] = htmlspecialchars($id3['artist'],ENT_QUOTES);
            $tag['albumName']  = htmlspecialchars($id3['album'],ENT_QUOTES);
            $tag['year']       = htmlspecialchars($id3['year'],ENT_QUOTES);
            $tag['comment']    = htmlspecialchars($id3['comment'],ENT_QUOTES);
            $tag['track']      = htmlspecialchars($id3['track'],ENT_QUOTES);
            $tag['genre']      = htmlspecialchars($id3['genre'],ENT_QUOTES);
            $tag['composer']   = ''; 
            $tag['publisher']  = '';
            $tag['provider']   = '';
            $tag['playTime']   = $fileInfo['playtime_seconds'];
            $tag['bitRate']    = $fileInfo['audio']['bitrate'];
            $tag['filesize']   = $fileInfo['filesize'];
            $tag['fileformat'] = $fileInfo['fileformat'];
            $tag['codec']      = $fileInfo['audio']['codec'];        
            $tag['sampleRate'] = $fileInfo['audio']['sample_rate'];
            $tag['dataFormat'] = $fileInfo['audio']['dataformat'];
            $tag['mimeType']   = $fileInfo['mime_type'];

            return $tag;
        } else {
            return false;
        }
    }


    /**
     * @access Public
     * @param String $guid
     * @param String $artistName
     * @param String $path
     * @return Int
     */
    private function _getArtistId($guid, $artistName,$path)
    {
        

        $artistId = (int)self::$artistDb->getArtistIdByGuid($guid);      

        // if no artistId create new artist
        if ($artistId < 1) {        

            // if we have AudioScrobbler enabled fetch extra information
            if (self::$config->media->useAudioscrobbler){
                require_once 'Audioscrobbler2.php';

                $as = new Zend_Service_Audioscrobbler;
                $as->set('api_key', self::$config->media->lastFMKey);
                $as->set('artist', $artistName);            

                $as->getArtistInfo();

                if ($as->getStatus() == 'ok') {
                    $bio   = $as->getArtistBio();
                    $image = $as->getArtistImages();
                        
                    $data = array(
                        'artist_guid'   => $guid,
                        'mbid'          => $as->getArtistMBID(),
                        'location'      => $path,
                        'description'   => $bio['content'],
                        'summary'       => $bio['summary'],
                        'urls'          => $as->getArtistUrl(),
                        'artist'        => self::_stripSlash($artistName),
                        'date_added'    => time()
                    );

                    // if download images
                    if (self::$config->media->downLoadImages) {                       

                        $fileSource = end($image['image']);

                        $filename = end(explode('/', $fileSource));                        
    
                        $imageDir = Zend_Registry::get('siteRootDir') ."/" . self::$config->media->imagePath. "/{$guid}" ;                         

                        if( !is_dir($imageDir) ) {
                            mkdir($imageDir, 0755);
                        }                  

                        $fileTarget = $imageDir . "/{$filename}";
                        
                        if ( self::download($fileSource, $fileTarget)) {
                            $data['image'] = self::$config->media->imagePath. "/{$guid}/{$filename}";
                        }
                    } else {
                        $data['image'] = $fileSource;
                    }
    
                    // get sim artists
                    $as = new Zend_Service_Audioscrobbler;
                    $as->set('api_key', self::$config->media->lastFMKey);
                    $as->set('artist', $artistName);   
                    $simular = $as->getAllArtistSimilar();
                    if ( $as->getStatus() == 'ok' ) {
                        $data['similar_artists'] = serialize($simular);
                    }

                    // get tags
                    $as = new Zend_Service_Audioscrobbler;
                    $as->set('api_key', self::$config->media->lastFMKey);
                    $as->set('artist', $artistName);   
                    $tags = $as->getArtistTopTags();
                    if ( $as->getStatus() == 'ok' ) {
                        $newTag = array();
                        // check tags against DB
                        $i = 0;

                        if( !empty($tags)) {

                            foreach($tags['tags'] as $tag) {

                                if(!empty($tag['name'])) {

                                   $genre = self::$genreDb->getGenreByName($tag['name']);
                                   
                                    if(!empty($genre) ) {
                                        $newTag[$i]['genre_name'] = $genre['genre_name'];
                                        $newTag[$i]['genre_id']   = $genre['genre_id'];
                                        $i++;
                                    }
                                }
                            }
                        }

                        

                        $data['genres'] = serialize($newTag);
                    }

                // status failed use defaults
                } else {                
                    $data = array(
                        'artist_guid'   => $guid,
                        'location'      => $path,   
                        'artist'        => self::_stripSlash($artistName),
                        'date_added'    => time()
                    );
                }
            // no api
            } else {
               $data = array(
                    'artist_guid'   => $guid,
                    'location'      => $path,
                    'artist'        => self::_stripSlash($artistName),
                    'date_added'    => time()
                );
            }

            // if no name set to unknown
            if(empty($data['artist'])) {
                $data['artist'] = 'unknown';
            }

            $artistId = self::$artistDb->newArtist($data);

            // map tags to genre DB
            if(!empty($newTag)){
                foreach($newTag as $tag){
                    $data = array(
                        'genre_id'  => $tag['genre_id'],
                        'artist_id' => $artistId
                    );
                    self::$genreDb->mapArtist($data);
                }
            }

            // fetch some images
            if (self::$config->media->downLoadImages) {
                $as = new Zend_Service_Audioscrobbler;
                $as->set('artist', $artistName); 
                $as->set('api_key', self::$config->media->lastFMKey);

                $images = $as->getAllArtistImages();
            
                $imageDir = Zend_Registry::get('siteRootDir') ."/" . self::$config->media->imagePath. "/{$guid}" ; 

                if ( $as->getStatus() == 'ok' ) {

                    for($i = 0; $i < count($images['images']); $i++) {
                   
                        $fileSource = $images['images'][$i]['sizes'][0];      
                    
                        $imageGuid = md5($fileSource);
                        
                        if(!self::$artistDb->checkArtistImage($imageGuid)) {

                            $filename = end(explode('/', $fileSource));

                            $fileTarget = $imageDir . "/{$filename}";

                            self::download($fileSource, $fileTarget);

                            $data = array(
                                'artist_id' => $artistId,
                                'guid'      => $imageGuid,
                                'title'     => self::_stripSlash($images['images'][$i]['title']),
                                'image'     => self::$config->media->imagePath. "/{$guid}/{$filename}",
                                'dateadded' => $images['images'][$i]['dateadded'],
                                'format'    => $images['images'][$i]['format'],
                            );
                            self::$artistDb->createNewImage($data);
                        }
                    }
                }
            }
           
            
        } 

        return $artistId;
    }


    /**
     * @access Public
     * @param Int $artistId
     * @param String $artistName
     * @param String $guid
     * @param String $albumName
     * @param String $path
     * @return Int
     */
    private function _getAlbumId($artistId, $artistName, $guid, $albumName, $path)
    {
        
        $albumId = (int)self::$albumDb->getAlbumIdByGuid($guid);

        // no album create it 
        if ($albumId < 1){

             // if we have AudioScrobbler enabled fetch extra information
            if (self::$config->media->useAudioscrobbler){
                require_once 'Audioscrobbler2.php';

                $as = new Zend_Service_Audioscrobbler;
                $as->set('api_key', self::$config->media->lastFMKey);
                $as->set('artist', $artistName);            
                $as->set('album', $albumName);
                $as->getAlbumInfo();

                if ($as->getStatus() == 'ok') {
                    $data = array(
                        'album_guid'    => $guid,
                        'last_fm_id'    => $as->getLastFMAlbumId(),
                        'mbid'          => $as->getAlbumMBID(),
                        'location'      => $path,
                        'released'      => $as->getReleaseDate(),
                        'artist_id'     => $artistId,
                        'title'         => self::_stripSlash($albumName),
                        'date_added'    => time()   
                    );

                    $image = $as->getAlbumImages();
                   
                    $fileSource = end($image);

                    // if we download images
                    if (self::$config->media->downLoadImages) {                       
                        
                        $filename = end(explode('/', $fileSource));                        
                
                        if(!empty($filename) ) {

                            $imageDir = Zend_Registry::get('siteRootDir') ."/" . self::$config->media->imagePath. "/{$guid}" ;                         

                            if( !is_dir($imageDir) ) {
                                mkdir($imageDir, 0755);
                            }                  

                            $fileTarget = $imageDir . "/{$filename}";
                            
                            if ( self::download($fileSource, $fileTarget)) {
                                $data['image'] = self::$config->media->imagePath. "/{$guid}/{$filename}";
                            }
                        }
                    } else {
                        // use link from last.fm
                        $data['image'] = $fileSource;
                    }

                    // genres
                    $tags = $as->getAlbumTopTags();
                    $data['genres'] = serialize($tags);

                // wasnt found use generic 
                } else {
                    $data = array(
                    'album_guid'    => $guid,
                    'artist_id'     => $artistId,
                    'location'      => $path,
                    'title'         => self::_stripSlash($albumName),
                    'date_added'    => time()   
                );
                }
            } else {
                $data = array(
                    'album_guid'    => $guid,
                    'artist_id'     => $artistId,
                    'location'      => $path,
                    'title'         => self::_stripSlash($albumName),
                    'date_added'    => time()   
                );
            }

            if(empty($data['title'])){
                $data['title'] = 'unknown';
            }

            $abumId = self::$albumDb->newAlbum($data);
             
            //  update new album count
            self::$artistDb->incrementAlbumCount($artistId);
        }

        return $albumId;
    }


    /**
     * @access Public
     * @param Int $playTime
     * @param Int $artistId
     * @param Int $albumId
     * @return Void
     */
    private function _updateCounts($playTime, $artistId, $albumId)
    {
        self::$artistDb->incrementTrackCount($artistId);

        self::$albumDb->incrementTrackCount($albumId);

        self::$albumDb->incrementPlayTime($albumId, $playTime);
    }


    /**
     * @access Public
     * @param String $text
     * @return String
     */
    private function _cleanText($text)
    {
        $find = array("/[^a-zA-Z0-9\s]/","/\s+/");
        $replace = array(" "," ");

        $newText = strtolower(preg_replace($find, $replace, $text));

        $newText = htmlspecialchars(ucfirst($newText), ENT_QUOTES);

        return $newText;

    }


    /**
     * @access Public
     * @param String $fileSource 
     * @param String $fileTarget
     * @return Bool 
     */
    function download($fileSource, $fileTarget)
    {    

        // Test to see if the file source is a real file
        if ( is_file( $fileSource ) )
        {
            throw new exception ("The file source " . $fileSource . " does not exist.");
        }

        $fileSource = str_replace(' ', '%20', html_entity_decode($fileSource));

        if (!file_exists($fileTarget)) 
        {
            // Test to see if we can write the file
            if ( is_writable( $fileTarget) )
            {
                throw new exception ("Unable to write file: " . $fileTarget);
            }

        
            if (($rh = fopen($fileSource, 'rb')) === FALSE) { 
                return false; 
            }

            if (($wh = fopen($fileTarget, 'wb')) === FALSE) { 
                return false; 
            }

            while (!feof($rh)){
                if (fwrite($wh, fread($rh, 1024)) === FALSE) { 
                    fclose($rh); 
                    fclose($wh); 
                    return false; 
                }
            }
            fclose($rh);
            fclose($wh);
        }
        return true;
    }

    
    /**
     * @access Public
     * @param String $text 
     * @return String 
     */
    private function _stripSlash($text)
    {
        $newText =  str_replace('/', '-', $text );

        return $newText;
    }

}
