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
class Zend_Service_MusicBrainz
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


    /**
     * Sets up character encoding, instantiates the HTTP client, and assigns the web service version.
     */
    public function __construct()
    {
        $this->set('version', '1.0');

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
            $this->getHttpClient()->setUri("http://musicbrainz.org/ws/1/{$service}");
        } else {
            $this->getHttpClient()->setUri("http://musicbrainz.org/ws/1/{$service}?{$params}");
        }

        $response     = $this->getHttpClient()->request();
        $responseBody = $response->getBody();

        if(!$response->isSuccessful()) {
            /**
             * @see Zend_Http_Client_Exception
             */
            require_once 'Zend/Http/Client/Exception.php';
            throw new Zend_Http_Client_Exception('The web service ' . $this->_client->getUri() . ' returned the following status code: ' . $response->getStatus());
        }

        set_error_handler(array($this, '_errorHandler'));

        if (!$simpleXmlElementResponse = simplexml_load_string($responseBody)) {
            restore_error_handler();
            /**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            $exception = new Zend_Service_Exception('Response failed to load with SimpleXML');
            $exception->error    = $this->_error;
            $exception->response = $responseBody;
            throw $exception;
        }

        restore_error_handler();

        return $simpleXmlElementResponse;
    }            


    /**
     * Utility function that returns the artist information by artist name
     *
     * @return SimpleXMLElement object containing result set
     */
    public function getArtistByName($name)
    {
        $service = "artist/";

        $params = "type=xml&name={$name}&limit=1&inc=tags+labels";

        $xml = $this->_getInfo($service, $params);
    
        $this->xml = $xml->{'artist-list'};     

        return $this->xml;
    }


    /**
     * Utility function that returns the artist information by artist MBID
     *
     * @return SimpleXMLElement object containing result set
     */
    public function getArtistByMBID($MBID)
    {
        $service = "artist/{$MBID}";
    
        $params = "type=xml&inc=tags+labels+ratings+counts+sa-Official+sa-Compilation";

        $this->xml = $this->_getInfo($service, $params);

        //print_r($this->xml);

        return $this->xml;
    }


    /** Standard Getters */

    /**
     * Utility function that returns the artist MBID
     *
     * @return String MBID
     */
    public function getArtistMBID()
    {   
        return (string)$this->xml->artist->attributes()->id;
    }


    /**
     * Utility function that returns the artist type
     *
     * @return String Artist Type
     */
    public function getArtistType()
    {
        return (string)$this->xml->artist->attributes()->type;
    }


    /**
     * Utility function that returns the artist name
     *
     * @return String Name
     */
    public function getArtistName()
    {
        return (string)$this->xml->artist->name;
    }


    /**
     * Utility function that returns the artist Sort Name
     *
     * @return String Sort Name
     */
    public function getArtistSortName()
    {
        return (string)$this->xml->artist->{'sort-name'};
    }


    /**
     * Utility function that returns the artist begine life
     *
     * @return String Begin Life
     */
    public function getArtistBeginLife()
    {
        return (string)$this->xml->artist->{'life-span'}->attributes()->begin;
    }


    /**
     * Utility function that returns the artist end life
     *
     * @return String End Life
     */
    public function getArtistEndLife()
    {
        return (string)$this->xml->artist->{'life-span'}->attributes()->end;
    }


    public function getArtistTags() 
    {
        return (array)$this->xml->artist->{'tag-list'};
    }


    public function getArtistReleases()
    {

        foreach((array)$this->xml->artist->{'release-list'} as $release) {

        }

        return ;
    }

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

}
