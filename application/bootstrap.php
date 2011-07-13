<?php
function __autoload($className) {
    require $className = str_replace('_', '/', $className) . '.php';
}
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
 * @package    Core
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: bootstrap.php 4 2009-6-1 Jaimie $
 */
class Bootstrap
{
    /* @access Public
     * @var object
     */
	private static $frontController = null;

    /* @access Public
     * @var object
     */
	private static $root            = '';

    /* @access Public
     * @var object
     */
	private static $registry        = null;

    /* @access Public
     * @var object
     */
    private static $view            = null;

    /* @access Public
     * @var object
     */
    private static $acl             = null;


    /** 
     * @access Public
     * return void
    */
	public static function run()
    {  
		self::prepare();        
		$response = self::$frontController->dispatch();
		         
	}

    /** 
     * @access Public
     * return void
    */
	public static function prepare()
    {
		self::setupEnvironment();
		self::setupRegistry();
		self::setupConfiguration();
		self::setupFrontController();
		self::setupErrorHandler();
		self::setupController();            
		self::setupView();
		self::setupDatabase();
		self::setupSessions();
		self::setupTranslation();
		self::setupRoutes();
		self::setupAcl();       
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupEnvironment()
    {
		error_reporting(E_ALL );
		ini_set('display_errors', true);
		self::$root = dirname(dirname(__FILE__));

        define('APPLICATION_ENVIRONMENT', 'default');		
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupRegistry() 
    {
		self::$registry = new Zend_Registry(array(), ArrayObject::ARRAY_AS_PROPS);
		Zend_Registry::setInstance(self::$registry);
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupConfiguration() 
    {
		$config = new Zend_Config_Ini( self::$root . '/application/Configs/Config.ini', APPLICATION_ENVIRONMENT );
		self::$registry->configuration = $config;

		//save $siteRootDir in registry:
		self::$registry->set('siteRootDir', self::$root );
		self::$registry->set('applicationRootDir', self::$root . '/application' );
		self::$registry->set('siteRootUrl', 'http://' . $_SERVER['HTTP_HOST'] . $config->config->appPath );

        // module Config
        $moduleConfig = new Zend_Config_Ini( self::$root . '/application/Configs/Module.ini', APPLICATION_ENVIRONMENT );
        self::$registry->set('moduleConfig', $moduleConfig);
	}

    
    /** 
     * @access Public
     * return void
    */
	public static function setupFrontController()
    {
		$moduleConifg =  self::$registry->get('moduleConfig');
       
        $moduleArray  = $moduleConifg->module;

        self::$frontController = Zend_Controller_Front::getInstance();

     

        foreach ($moduleArray as $module) {
            if ( $module->enabled == 1) {
                self::$frontController->addControllerDirectory(
                    self::$root . '/application/Modules/'.$module->directory.'/'.$module->controler,
                    $module->name
                );
            }    
        }        

        self::$frontController->throwExceptions($moduleConifg->config->throwExceptions);
		self::$frontController->returnResponse($moduleConifg->config->returnResponse);        
        self::$frontController->setParam('useDefaultControllerAlways', $moduleConifg->config->useDefaultControllerAlways);
		self::$frontController->setParam('env', APPLICATION_ENVIRONMENT);
        self::$frontController->setParam('registry', self::$registry);

		$response = new Zend_Controller_Response_Http; 
		

		self::$frontController->setResponse($response);     
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupErrorHandler() 
    {
		self::$frontController->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(
		    array(
			    'module'	 => 'default',
			    'controller' => 'error',
			    'action'	 => 'error')
		    ));
		$writer = new Zend_Log_Writer_Firebug();
		$logger = new Zend_Log($writer);
		Zend_Registry::set('logger',$logger);
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupController()
    {
		// place to put in your Controll Action Helpers
		// ex: Zend_Controller_Action_HelperBroker::addHelper(new GSD_Controller_Action_Helper_AuthUsers());
            
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupView() 
    {
		self::$view = new Zend_View(array('encoding'=>'UTF-8'));
    
		$viewRendered = new Zend_Controller_Action_Helper_ViewRenderer(self::$view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRendered);
            

		$layout = Zend_Layout::startMvc(
			array(
				'layoutPath'  => self::$root . '/application/Layouts',
				'layout'      => 'Main',
                'pluginClass' => 'Helper_Layout',
			)
		);

        self::$registry->set('Zend_Layout', $layout);
        
        

        // assign some configs
        self::$view->configuration = self::$registry->get('configuration');
        self::$view->moduleConfig  = self::$registry->get('moduleConfig');

        self::$registry->set('view', self::$view);


	}

    /** 
     * @access Public
     * return void
    */
	public static function setupDatabase()
    {

        if ( self::$registry->configuration->installed) {

            $dbConfig = new Zend_Config_Ini( self::$root . '/application/Configs/Database.ini', 'default' );

            $db = Zend_Db::factory($dbConfig); 

            // profiler
            $profiler = new Zend_Db_Profiler_Firebug('All DB Queries');
            $profiler->setEnabled(true);

            // Attach the profiler to your db adapter
            $db->setProfiler($profiler);
            
            self::$registry->set('Zend_Db', $db);
        } 
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupSessions()
    {
        Zend_Session::start();
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupTranslation()
    {
        date_default_timezone_set('America/Los_Angeles');		

		if (self::$frontController) {
			self::$frontController->registerPlugin( new Helper_Translate());       
		}
	}


    /** 
     * @access Public
     * return void
    */
	public static function setupRoutes()
    {
        $router = self::$frontController->getRouter();

        if ( self::$registry->configuration->installed) {

            /** album view route */
            $route  = new Zend_Controller_Router_Route(
                'album/:alpha/:page',
                array( 
                        'module'     => 'album', 
                        'controller' => 'index', 
                        'action'     => 'index',
                        'alpha'      => 'All',
                        'page'       => '',
                          )
            );
            $router->addRoute('album',  $route);

            /** album add route */
            $route  = new Zend_Controller_Router_Route(
                'album/add/:artist',
                array( 
                        'module'     => 'album', 
                        'controller' => 'add', 
                        'action'     => 'index',
                        'artist'      => '',
                          )
            );
            $router->addRoute('album-add',  $route);
            
            /** album edit route */
            $route  = new Zend_Controller_Router_Route(
                'album/edit/:name',
                array( 
                        'module'     => 'album', 
                        'controller' => 'edit', 
                        'action'     => 'index',
                        'name'      => '',
                          )
            );
            $router->addRoute('album-edit',  $route);
            

            /** play route */
            $route  = new Zend_Controller_Router_Route(
                'play/track/:id',
                array( 
                        'module'     => 'play', 
                        'controller' => 'track', 
                        'action'     => 'index',
                        'id'         => ''
                          )
            );
            $router->addRoute('play-track',  $route);

            /** mp3 route */
            $route  = new Zend_Controller_Router_Route(
                'play/:tracks',
                array( 
                        'module'     => 'play', 
                        'controller' => 'index', 
                        'action'     => 'index',
                        'tracks'     => ''
                          )
            );
            $router->addRoute('play',  $route);

            /** All Artis view Route */
            $route  = new Zend_Controller_Router_Route(
                'artist/:alpha/:page',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'index', 
                        'action'     => 'index',
                        'alpha'      => 'All',
                        'page'       => '',
                          )
            );
            $router->addRoute('artist',  $route);
           
            /** Artis view Route */
            $route  = new Zend_Controller_Router_Route(
                'artist/view/:name/:album/:track/*',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'view', 
                        'action'     => 'index',
                        'name'       => '',
                        'album'      => '',
                        'track'      => '')
            );
            $router->addRoute('artist-view',  $route);

            /** More Artist Route */
            $route  = new Zend_Controller_Router_Route(
                'artist/more/:name/*',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'more', 
                        'action'     => 'index',
                        'name'       => '',)
            );
            $router->addRoute('artist-more',  $route);

            /** Artist Picture Route */
            $route  = new Zend_Controller_Router_Route(
                'artist/pictures/:name/*',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'pictures', 
                        'action'     => 'index',
                        'name'       => '',)
            );
            $router->addRoute('artist-pictures',  $route);

            /** Artist Picture Route */
            $route  = new Zend_Controller_Router_Route(
                'artist/events/:name',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'event', 
                        'action'     => 'index',
                        'name'       => '',)
            );
            $router->addRoute('artist-events',  $route);


            /** edit Artist route */
            $route  = new Zend_Controller_Router_Route(
                'artist/edit/:name/*',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'edit', 
                        'action'     => 'index',
                        'name'       => '',)
            );
            $router->addRoute('artist-edit',  $route);

            /** artist add route */
            $route  = new Zend_Controller_Router_Route(
                'artist/add/',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'add', 
                        'action'     => 'index',
                        'name'       => '',)
            );
            $router->addRoute('artist-add',  $route);
            
            /** artist rss route */
            $route  = new Zend_Controller_Router_Route(
                'artist/rss/',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'rss',)
            );
            $router->addRoute('artist-rss',  $route);

            /** artist api route */
            $route  = new Zend_Controller_Router_Route(
                'artist/soap/',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'soap', )
            );
            $router->addRoute('artist-soap',  $route);

            /** artist api route */
            $route  = new Zend_Controller_Router_Route(
                'artist/json/',
                array( 
                        'module'     => 'artist', 
                        'controller' => 'json', )
            );
            $router->addRoute('artist-json',  $route);

            /** file add route */
            $route  = new Zend_Controller_Router_Route(
                'file/add/:album',
                array( 
                        'module'     => 'file', 
                        'controller' => 'add', 
                        'action'     => 'index',
                        'album'      => '',)
            );
            $router->addRoute('file-add',  $route);

            /** file edit route */
            $route  = new Zend_Controller_Router_Route(
                'file/edit/:track',
                array( 
                        'module'     => 'file', 
                        'controller' => 'edit', 
                        'action'     => 'index',
                        'track'      => '',)
            );
            $router->addRoute('file-add',  $route);

            /** Genre  route */
             /** Genre  route */
            $route  = new Zend_Controller_Router_Route(
                'genre/jason/:action',
                array( 
                        'module'     => 'genre', 
                        'controller' => 'json',
                        'action'     => '',
                          )
            );
            $router->addRoute('genre',  $route);


            $route  = new Zend_Controller_Router_Route(
                'genre/:name',
                array( 
                        'module'     => 'genre', 
                        'controller' => 'index', 
                        'action'     => 'index',
                        'name'       => '',
                          )
            );
            $router->addRoute('genre',  $route);

        } else {
           
            $route  = new Zend_Controller_Router_Route(
                '',
                array( 
                        'module'     => 'install', 
                        'controller' => 'index', 
                        'action'     => '',)
            );
            $router->addRoute('installer',  $route);

        }

	}


    /** 
     * @access Public
     * return void
    */
	public static function setupAcl()
    {
		
        self::$acl = new Zend_Acl();
        self::$acl->addRole( new Zend_Acl_Role('Guest') );
        self::$acl->addRole( new Zend_Acl_Role('Member') ,'Guest' );
        self::$acl->addRole( new Zend_Acl_Role('PowerMember'), 'Member');
        self::$acl->addRole( new Zend_Acl_Role('Administrator'), 'PowerMember' );

        self::$view->auth = Zend_Auth::getInstance();
        self::$view->acl  = self::$acl;               
	}


}
