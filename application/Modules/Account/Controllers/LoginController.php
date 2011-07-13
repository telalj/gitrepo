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
 * @version    $Id: LoginController.php 4 2009-6-1 Jaimie $
 */
class Account_LoginController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $auth        = null;

    /* @access Public
     * @var object
     */
    private static $acl         = null;

    /* @access Public
     * @var object
     */
    private static $db          = null;

    /* @access Public
     * @var object
     */
    private static $form        = null;

    /* @access Public
     * @var object
     */
    private static $accountDb   = null;

    /* @access Public
     * @var object
     */
    private static $config        = null;


    /** 
     * @access Public
     * @return Void
     */
    public function init()
    {

        // load registry
        $registry = Zend_Registry::getInstance();

        self::$config = $registry->get('moduleConfig');

        if ( !$registry->get('moduleConfig')->module->account->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Account');
        }

        self::$form      = new Model_Account_Form;
        self::$accountDb = new Model_Account_Db;
       
        self::$db           = $registry->get('Zend_Db');
        $this->_siteRootDir = $registry->get('siteRootDir');  
    }


    /** 
     * @access Public
     * @return Void
     */
	public function indexAction()
    {
        Zend_Registry::set('layout','3_column');

        $from = (string)$this->getRequest()->getParam('from');
        $this->view->from = $from;

        $form = self::$form->LoginForm($from);

        // Post 
        if ($this->getRequest()->isPost()) {
            if (!$form->isValid($_POST)) {
                $this->view->errorMsg = $this->view->translate('Sign_in_Failed');
                $this->view->form = $form;             
            } else {
                $request 	= $this->getRequest();   	
		        $auth		= Zend_Auth::getInstance();
            
                Zend_Db_Table_Abstract::setDefaultAdapter(self::$db);

                $authAdapter = new Zend_Auth_Adapter_DbTable(self::$db);
                $authAdapter->setTableName('account')->setIdentityColumn('account_username')->setCredentialColumn('account_password');

                 // Log it and update info
                $stream = @fopen($this->_siteRootDir.'/log/access', 'a', false);
                if (! $stream) {
                    throw new Exception('Failed to open log file: access');
                }
                $writer = new Zend_Log_Writer_Stream($stream);
                $logger = new Zend_Log($writer);

                // Get users Ip address
                $visitorIp = self::$accountDb->getVisitorIP();

                // Set the input credential values
                $uname = $request->getParam('account_username');
                $paswd = $request->getParam('account_password');

                // Set the auth Adapter
                $authAdapter->setIdentity($uname);
                $authAdapter->setCredential(md5($paswd));

                // Perform the authentication query, saving the result
                $result = $auth->authenticate($authAdapter);

                // If login is valid
                if($result->isValid()){
            
                  
                    // Load and store data into the session
                    $data = $authAdapter->getResultRowObject(null,'account_password');
                    
                 

                    // check account status
                    if($data->account_status == 1) {
                        // if invite registration send to the invite code
                        if(self::$config->module->account->register == 'invite') {
                            $auth->clearIdentity();
                            $this->_redirect('account/invite');
                        }

                        // verify go to the email activate
                        if (self::$config->module->account->register == 'verify') {
                            $auth->clearIdentity();
                            $this->_redirect('account/activate');                                            
                        }
                    }

                    // If account is suspended 
                    if ($data->account_status == 3) {
                        $auth->clearIdentity();
                        $this->_redirect('account/suspended');
                    }


                    $auth->getStorage()->write($data);

                    // Log the login
                    $logger->addWriter($writer);
                    $logger->log("Login: User: " . $request->getParam('account_username') .": IP Address: " . $visitorIp, Zend_Log::INFO);

                    // Update the account with last login and login from
                    $accountData = array (
                        'last_login' => time(),
                        'login_ip'   => $visitorIp,
                        'session_id' => session_id()
                    );                  
                    self::$accountDb->updateAccount($accountData,$data->account_id);

                    // Check to see if we have a page to redirect otherwise take to the account page
                    $defaultNamespace = new Zend_Session_Namespace('Default');
                    if($defaultNamespace->redirectPage) {
                        $this->_redirect($defaultNamespace->redirectPage);
                    } else {

                        // if we requested a page then go there else go to account                        
                        if ( !empty($from) )
                        {
                            $from = str_replace(":", "/", $this->getRequest()->getParam('from'));
                            $this->_redirect("/".$from);
                        } else {
                            $this->_redirect('account/index');
                        }
                    }
                     
                }else{
                    // Credentials failed display the login

                    // Record failed Login
                    $logger->addWriter($writer);
                    $logger->log("Failed Login: User: " . $request->getParam('account_contact_email') .": IP Address: " . $visitorIp, Zend_Log::WARN);

                    $this->view->errorMsg = $this->view->translate('Sign_in_Failed');
                    // Display the form
                    $this->view->form = $form;
                }
            }
        } else {
            $this->view->form = self::$form->LoginForm($from);
        }

	}

} 
