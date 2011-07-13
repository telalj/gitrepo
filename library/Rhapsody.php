<?php

require_once 'Zend/Http/Client.php';

class Zend_Service_Rhapsody
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

        $this->getHttpClient()->setUri($service);
        

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


    
    public function getTopGenes()
    {
        $service = 'http://feeds.rhapsody.com/data.xml';
        
        $this->xml = $this->_getInfo($service);

        $array = array();
        $i = 0;
        foreach($this->xml->{'sub-genres'}->{'sub-genre'} as $subGenres) {
            //print_r($subGenres);
            $array[$i]['name']  = (string)$subGenres->attributes()->name;
            $array[$i]['link'] = (string)$subGenres->{'data-href'};
            $i++;
        }

        return $array;
    }
    
    public function getSubGenre($service)
    {
        $this->xml = $this->_getInfo($service);

        $array = array();
        $i = 0;
        
        if(!empty($this->xml->{'sub-genres'}->{'sub-genre'})) {

            foreach($this->xml->{'sub-genres'}->{'sub-genre'} as $subGenres) {
                //print_r($subGenres);
                $array[$i]['name']  = (string)$subGenres->attributes()->name;
                $array[$i]['link'] = (string)$subGenres->{'data-href'};
                $i++;
            }
        }

        return $array;
    }


    /** Utility function to printout raw XML
    */
    public function getXML()
    {
        return $this->xml;
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

    
    /**
     * Call Intercept for set($name, $field)
     *
     * @param  string $method
     * @param  array  $args
     * @return Zend_Service_Audioscrobbler
     */
    public function __call($method, $args)
    {
        
    }

}


