<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Audioscrobbler
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Audioscrobbler.php 14809 2009-04-09 19:01:40Z beberlei $
 */


/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';


/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Audioscrobbler
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Audioscrobbler
{
    /**
     * Zend_Http_Client Object
     *
     * @var     Zend_Http_Client
     * @access  protected
     */
    protected $_client;


    /**
     * Array that contains parameters being used by the webservice
     *
     * @var     array
     * @access  protected
     */
    protected $_params;


    /**
     * Holds error information (e.g., for handling simplexml_load_string() warnings)
     *
     * @var     array
     * @access  protected
     */
    protected $_error = null;


    protected $xml;

    /**
     * Sets up character encoding, instantiates the HTTP client, and assigns the web service version.
     */
    public function __construct()
    {
        $this->set('version', '2.0');

        iconv_set_encoding('output_encoding', 'UTF-8');
        iconv_set_encoding('input_encoding', 'UTF-8');
        iconv_set_encoding('internal_encoding', 'UTF-8');
    }


    /**
     * Set Http Client
     * 
     * @param Zend_Http_Client $client
     */
    public function setHttpClient(Zend_Http_Client $client)
    {
        $this->_client = $client;
    }

    /**
     * Get current http client.
     * 
     * @return Zend_Http_Client
     */
    public function getHttpClient()
    {
        if($this->_client == null) {
            $this->lazyLoadHttpClient();
        }
        return $this->_client;
    }


    /**
     * Lazy load Http Client if none is instantiated yet.
     *
     * @return void
     */
    protected function lazyLoadHttpClient()
    {
        $this->_client = new Zend_Http_Client();
    }


    /**
     * Returns a field value, or false if the named field does not exist
     *
     * @param  string $field
     * @return string|false
     */
    public function get($field)
    {
        if (array_key_exists($field, $this->_params)) {
            return $this->_params[$field];
        } else {
            return false;
        }
    }


    /**
     * Generic set action for a field in the parameters being used
     *
     * @param  string $field name of field to set
     * @param  string $value value to assign to the named field
     * @return Zend_Service_Audioscrobbler Provides a fluent interface
     */
    public function set($field, $value)
    {
        $this->_params[$field] = urlencode($value);

        return $this;
    }


    /**
     * Protected method that queries REST service and returns SimpleXML response set
     *
     * @param  string $service name of Audioscrobbler service file we're accessing
     * @param  string $params  parameters that we send to the service if needded
     * @throws Zend_Http_Client_Exception
     * @throws Zend_Service_Exception
     * @return SimpleXMLElement result set
     * @access protected
     */
    protected function _getInfo($service, $params = null)
    {
        $service = (string) $service;
        $params  = (string) $params;
        
        if ($params === '') {
            $this->getHttpClient()->setUri("http://ws.audioscrobbler.com{$service}");
        } else {
            $this->getHttpClient()->setUri("http://ws.audioscrobbler.com{$service}?{$params}");
        }

        $response     = $this->getHttpClient()->request();
        $responseBody = $response->getBody();

        if (!$response->isSuccessful()) {
            return false;     
        }

        if(empty($responseBody)){
            return false;
        }
       

        

        if (!$simpleXmlElementResponse = simplexml_load_string($responseBody)) {
            return false;
        }



    

        return $simpleXmlElementResponse;
    }            


    /** New functions here */

    
    public function getArtistInfo()
    {
        $service = "/{$this->get('version')}/?method=artist.getinfo&artist={$this->get('artist')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);
        return $this->xml;
    }


    public function getArtistEvents()
    {
        $service = "/{$this->get('version')}/?method=artist.getevents&artist={$this->get('artist')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);

        $array = array();

        $array['artist'] = (string)$this->xml->events->attributes()->artist;
        $array['total']  = (string)$this->xml->events->attributes()->total;
        $array['events'] = array();

        for($i = 0; $i < count($this->xml->events->event); $i++) {
            $array['events'][$i]['id']    = (int)$this->xml->events->event[$i]->id;
            $array['events'][$i]['title'] = (string)$this->xml->events->event[$i]->title;
            $array['events'][$i]['artist'] = array();

            // artists
            for($b = 0; $b < count($this->xml->events->event[$i]->artists->artist); $b++ ) {
                $array['events'][$i]['artist'][$b] = (string)$this->xml->events->event[$i]->artists->artist[$b];
            }
            $array['events'][$i]['headliner'] = (string)$this->xml->events->event[$i]->artists->headliner;

            // venue
            $array['events'][$i]['venue'] = array();
            $array['events'][$i]['venue']['name'] = (string)$this->xml->events->event[$i]->venue->name;
            $array['events'][$i]['venue']['location'] = array();
            $array['events'][$i]['venue']['location']['city'] = (string)$this->xml->events->event[$i]->venue->location->city;
            $array['events'][$i]['venue']['location']['country'] = (string)$this->xml->events->event[$i]->venue->location->country;
            $array['events'][$i]['venue']['location']['street'] = (string)$this->xml->events->event[$i]->venue->location->street;
            $array['events'][$i]['venue']['location']['postalcode'] = (string)$this->xml->events->event[$i]->venue->location->postalcode;
            $array['events'][$i]['venue']['location']['timezone'] = (string)$this->xml->events->event[$i]->venue->location->timezone;
            $array['events'][$i]['venue']['url'] = (string)$this->xml->events->event[$i]->venue->url;
            
            // times
            $array['events'][$i]['startDate'] = (string)$this->xml->events->event[$i]->startDate;
            $array['events'][$i]['startTime'] = (string)$this->xml->events->event[$i]->startTime;
            $array['events'][$i]['description'] = (string)$this->xml->events->event[$i]->description;

            // images
            $array['events'][$i]['image'] = array();
            for($c = 0; $c < count($this->xml->events->event[$i]->image); $c++) {
                $array['events'][$i]['image'][$c] = (string)$this->xml->events->event[$i]->image[$c];
            }

            $array['events'][$i]['attendance'] = (string)$this->xml->events->event[$i]->attendance;
            $array['events'][$i]['reviews'] = (string)$this->xml->events->event[$i]->reviews;
            $array['events'][$i]['tag'] = (string)$this->xml->events->event[$i]->tag;
            $array['events'][$i]['url'] = (string)$this->xml->events->event[$i]->url;
            $array['events'][$i]['website'] = (string)$this->xml->events->event[$i]->website;

            // ticket locations
            $array['events'][$i]['tickets'] = array();
            for($d = 0; $d < count($this->xml->events->event[$i]->tickets); $d++) {
                $array['events'][$i]['tickets'][$d] = (string)$this->xml->events->event[$i]->tickets[$d]->ticket;
            }
        }

        return $array;
    }
    

    public function getAllArtistImages()
    {
        $service = "/{$this->get('version')}/?method=artist.getimages&artist={$this->get('artist')}&api_key={$this->get('api_key')}&page={$this->get('page')}";
        $this->xml = $this->_getInfo($service);

        if ($this->getStatus() != 'ok') {
            return false;
        }

        $array = array();
        $array['artist'] = (string)$this->xml->images->attributes()->artist;
        $array['page'] = (int)$this->xml->images->attributes()->page;
        $array['totalpages'] = (int)$this->xml->images->attributes()->totalpages;
        $array['total'] = (int)$this->xml->images->attributes()->total;

        // Images
        $array['images'] = array();
        for($i = 0; $i < count($this->xml->images->image); $i++) {
            $array['images'][$i]['title'] = (string)$this->xml->images->image[$i]->title;
            $array['images'][$i]['url'] = (string)$this->xml->images->image[$i]->url;
            $array['images'][$i]['dateadded'] =  (string)$this->xml->images->image[$i]->dateadded;
            $array['images'][$i]['format'] = (string)$this->xml->images->image[$i]->format;

            // owners
            $array['images'][$i]['owner'] = array();
            @$array['images'][$i]['owner']['type'] = (string)$this->xml->images->image[$i]->owner->attributes()->type;
            @$array['images'][$i]['owner']['name'] = (string)$this->xml->images->image[$i]->owner->name;
            @$array['images'][$i]['owner']['url'] = (string)$this->xml->images->image[$i]->owner->url;

            // sizes
            $array['images'][$i]['sizes'] = array();           
            for($c = 0; $c < count($this->xml->images->image[$i]->sizes->size); $c++) {
                $array['images'][$i]['sizes'][$c] = (string)$this->xml->images->image[$i]->sizes->size[$c];
            }

            $array['images'][$i]['votes'] = array();
            $array['images'][$i]['votes']['thumbsup'] = (int)$this->xml->images->image[$i]->votes->thumbsup;
            $array['images'][$i]['votes']['thumbsdown'] = (int)$this->xml->images->image[$i]->votes->thumbsdown;
        }
        
        return $array;
    }


    public function getArtistTopTags()
    {
        $service = "/{$this->get('version')}/?method=artist.gettoptags&artist={$this->get('artist')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);

        if ($this->getStatus() != 'ok') {
            return false;
        }

        $array = array();

        $array['artist'] = (string)$this->xml->toptags->attributes()->artist;
        $array['tags'] = array();

        for($i = 0; $i < count($this->xml->toptags->tag); $i++ ) {
            $array['tags'][$i]['name']  = (string)$this->xml->toptags->tag[$i]->name;
            $array['tags'][$i]['count'] = (int)$this->xml->toptags->tag[$i]->count;
            $array['tags'][$i]['url']   = (string)$this->xml->toptags->tag[$i]->url;
        }

        return $array;
    }


    public function getAllArtistSimilar()
    {
        $service = "/{$this->get('version')}/?method=artist.getsimilar&artist={$this->get('artist')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);

        $array = array();

        $array['artist'] = (string)$this->xml->similarartists->attributes()->artist;
        $array['similar'] = array();

        for($i = 0; $i < count($this->xml->similarartists->artist); $i++) {
            $array['similar'][$i]['name']  = (string)$this->xml->similarartists->artist[$i]->name;
            $array['similar'][$i]['mbid'] = (string)$this->xml->similarartists->artist[$i]->mbid;
            $array['similar'][$i]['match'] = (float)$this->xml->similarartists->artist[$i]->match;
            $array['similar'][$i]['url'] = (string)$this->xml->similarartists->artist[$i]->url;

            $array['similar'][$i]['image'] = array();
            for($c = 0; $c < count($this->xml->similarartists->artist[$i]->image); $c++ ) {
                $array['similar'][$i]['image'][$c] = (string)$this->xml->similarartists->artist[$i]->image[$c];
            }

            $array['similar'][$i]['streamable'] = (int)$this->xml->similarartists->artist[$i]->streamable;
        }

        return $array;
    }


    public function getArtistTopTracks()    
    {
        $service = "/{$this->get('version')}/?method=artist.gettoptracks&artist={$this->get('artist')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);
        
        $array = array();


        return $array;
    }


    /** getters */

    public function getStatus()
    {
             
        if (!empty($this->xml)){
            $status = (string)$this->xml->attributes()->status;
        }else {
            $status = 'fail';
        }
           
        return $status;
    }


    public function getArtistName()
    {
        return (string)$this->xml->artist->name;
    }


    public function getArtistMBID()
    {
        return (string)$this->xml->artist->mbid;
    }


    public function getArtistUrl()
    {
        return (string)$this->xml->artist->url;
    }


    public function getArtistImages()
    {
        $array = array();
    
        for($i = 0; $i < count($this->xml->artist->image); $i++) {
            $array['image'][$i] = (string)$this->xml->artist->image[$i];
        }

        return $array;
    }


    public function getArtistStreamable()
    {
        return (int)$this->xml->artist->streamable;
    }


    public function getArtistStats()
    {
        $array = array(
            'listeners' => (int)$this->xml->artist->stats->listeners, 
            'playcount' => (int)$this->xml->artist->stats->playcount
        );

        return $array;
    }


    public function getArtistSimilar()
    {
        $array = array();
       
        for($i = 0; $i < count($this->xml->artist->similar->artist); $i++) {
            $array[$i]['name']  = (string)$this->xml->artist->similar->artist[$i]->name;
            $array[$i]['url']   = (string)$this->xml->artist->similar->artist[$i]->url;
            for($b = 0; $b < count($this->xml->artist->similar->artist[$i]->image); $b++) {
                $array[$i]['image'][$b] = (string)$this->xml->artist->similar->artist[$i]->image[$b];
            }   
        }

        return $array;
    }


    public function getArtistBio()
    {
        $array = array(
            'published' => (string)$this->xml->artist->bio->published,
            'summary'   => (string)$this->xml->artist->bio->summary,
            'content'   => (string)$this->xml->artist->bio->content
        );

        return $array;
    }
    

    /** Albums */

    public function getAlbumInfo()
    {
        $service = "/{$this->get('version')}/?method=album.getinfo&artist={$this->get('artist')}&album={$this->get('album')}&api_key={$this->get('api_key')}";
        $this->xml = $this->_getInfo($service);
        return $this->xml;
    }

    public function getAlbumName()
    {
        return (string)$this->xml->album->name;
    }


    public function getAlbumArtist()
    {
        return (string)$this->xml->album->artist;
    }

    public function getLastFMAlbumId()
    {
        return (string)$this->xml->album->id;
    }

    public function getReleaseDate()
    {
        return (string)$this->xml->album->releasedate;
    }

    public function getAlbumMBID()
    {
        return (string)$this->xml->album->mbid;
    }

    public function getAlbumUrl()
    {
        return (string)$this->xml->album->url;
    }


    public function getAlbumImages()
    {
        $array = array();
           

        for($i = 0; $i < count($this->xml->album->image); $i++) {
            $array[$i] = (string)$this->xml->album->image[$i];
        }

        return $array;
    }


    public function getAlbumListeners()
    {
        return (int)$this->xml->album->listeners;
    }


    public function getAlbumPlaycount()
    {
        return (int)$this->xml->album->playcount;
    }

    
    public function getAlbumTopTags()
    {
        $array = array();

        for($i = 0; $i < count($this->xml->album->toptags->tag); $i++) {
            $array['tag'][$i]['name'] = (string)$this->xml->album->toptags->tag[$i]->name;
            $array['tag'][$i]['url'] = (string)$this->xml->album->toptags->tag[$i]->url;
        }

        return $array;        
    }

    
    public function getAlbumBio()
    {
        $array = array();

        $array['bio']['published'] = (string)$this->xml->album->wiki->published;
        $array['bio']['summary'] = (string)$this->xml->album->wiki->summary;
        $array['bio']['content'] = (string)$this->xml->album->wiki->content;

        return $array;  
    }


    /** Utility function to printout raw XML
    */
    public function getXML()
    {
        return $this->xml;
    }

    /** EOF New functions here */

    /**
     * Saves the provided error information to this instance
     *
     * @param  integer $errno
     * @param  string  $errstr
     * @param  string  $errfile
     * @param  integer $errline
     * @param  array   $errcontext
     * @return void
     */
    protected function _errorHandler($errno, $errstr, $errfile, $errline, array $errcontext)
    {
        $this->_error = array(
            'errno'      => $errno,
            'errstr'     => $errstr,
            'errfile'    => $errfile,
            'errline'    => $errline,
            'errcontext' => $errcontext
            );
    }

    
    /**
     * Call Intercept for set($name, $field)
     *
     * @param  string $method
     * @param  array  $args
     * @return Zend_Service_Audioscrobbler
     */
    public function __call($method, $args)
    {
        if(substr($method, 0, 3) !== "set") {
            require_once "Zend/Service/Exception.php";
            throw new Zend_Service_Exception(
                "Method ".$method." does not exist in class Zend_Service_Audioscrobbler."
            );
        }
        $field = strtolower(substr($method, 3));

        if(!is_array($args) || count($args) != 1) {
            require_once "Zend/Service/Exception.php";
            throw new Zend_Service_Exception(
                "A value is required for setting a parameter field."
            );
        }
        $this->set($field, $args[0]);

        return $this;
    }

}
