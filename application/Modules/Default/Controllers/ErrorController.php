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
 * @package    Account
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: ErrorController.php 4 2009-6-1 Jaimie $
 */
class ErrorController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $acl        = null;

    /* @access Public
     * @var object
     */
    private static $auth       = null;

    /** 
     * @access Public
     * @return void
     */
    public function init()
    {
        self::$acl         = $this->view->acl; 

        self::$auth        = $this->view->auth;     
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {


    }


    /** 
     * @access Public
     * @return void
     */
    public function errorAction()
    {
    
        $errors = $this->_getParam ('error_handler') ;


        switch ($errors->type) {

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :

            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
                // 404 error -- controller or action not found
                $this->getResponse ()->setRawHeader ( 'HTTP/1.1 404 Not Found' ) ;
                // ... get some output to display...
                
            break ;
            default :
                // application error; display error page, but don't change            
                // status code
                $this->view->errorTitle .= "Error!";

                $this->view->message = "<p>An unexpected error occurred with your request. Please try again later.</p>";

                // Log the exception
                $exception = $errors->exception;
                $log = new Zend_Log( new Zend_Log_Writer_Stream(Zend_Registry::get('siteRootDir').'/log/exceptions_log'));
                $log->debug( $exception->getMessage() . PHP_EOL . $exception->getTraceAsString() );

           break ;
        }

        // Clear previous content
        $this->getResponse()->clearBody();         
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function moduleNotEnabledAction()
    {
        
       $this->view->module = $this->getrequest()->getParam('mod');
    }


    /** 
     * @access Public
     * @return void
     */
    public function artistNotFoundAction()
    {
        $artist = (string)urldecode($this->getRequest()->getParam('artist'));

        $this->view->artist = $artist;       
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function albumNotFoundAction()
    {
        $artist = (string)urldecode($this->getRequest()->getParam('artist'));
        $album  = (string)urldecode($this->getRequest()->getParam('album'));

        $this->view->album  = $album;
        $this->view->artist = $artist; 
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function accessDeniedAction()
    {
        $from  = $this->getRequest()->getParam('from');
        $action = $this->getRequest()->getParam('action');

        if(!self::$auth->hasIdentity()) {
            $this->_redirect('account/login/index/from/' . $from);
        } else {
            $this->view->from = $from;
            $this->view->action = $action;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function artistImageNotFoundAction()
    {

    }
}
