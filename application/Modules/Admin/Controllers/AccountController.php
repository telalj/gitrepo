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
 * @package    Admin
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: AccounController.php 4 2009-6-1 Jaimie $
 */
class Admin_AccountController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $accountDb   = null;
    
    /* @access Public
     * @var object
     */
    private static $accountForm = null;

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
    private static $config     = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {
        // load registry
        $registry = Zend_Registry::getInstance();

        self::$config = $registry->get('moduleConfig')->module->admin;
        self::$acl    = $this->view->acl;        
        self::$auth   = $this->view->auth;
        
        /** Acls */
        if(self::$auth->hasIdentity()) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }
        
        foreach (self::$config->acl as $key => $val) {
            $resourceArray = explode(':', $val);
            self::$acl->allow($key,  null,   $resourceArray);            
        }    

        if(!self::$acl->isAllowed($accountType, null, 'admin') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/admin:account');
        }

        self::$accountDb     = new Model_Account_Db;

        self::$accountForm   = new Model_Account_Form;    
    }
    
    
    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
       $page = $this->getRequest()->getParam('page');

       $paginator = self::$accountDb->getAccounts($page);
        
       $this->view->paginator = $paginator;
    }


    /** 
     * @access Public
     * @return void
     */
    public function viewAction()
    {

        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        } 

        $userData = self::$accountDb->getAccount($accountId);
        // if empty data show error.
        if ( empty($userData) ) {
            $this->render('account-not-found');
        }
        $this->view->userData = $userData;
        
        // get billing address            
        $this->view->billingAddress  = self::$accountDb->getAddress('billing', $accountId);

        // get shipping address
        $this->view->shippingAddress = self::$accountDb->getAddress('shipping', $accountId);         
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function addAction()
    {
        $form = self::$accountForm->adminRegisterForm();

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
                 
                // activation code
                    $accountInviteCode = self::$accountDb->getNewCode(16);

                    // register ip
                    $loginIp = self::$accountDb->getVisitorIP();

                    $data = array (
                        'account_firstname'     => $values['account_firstname'],
                        'account_lastname'      => $values['account_lastname'],
                        'account_email'         => $values['account_email'],
                        'account_telephone'     => $values['account_telephone'],
                        'account_alt_telephone' => $values['account_alt_telephone'],
                        'account_receive_email' => $values['account_receive_email'],
                        'account_email_type'    => $values['account_email_type'],
                        'account_password'      => md5($values['account_password']),
                        'account_username'      => $values['account_username'],
                        'account_status'        => 2,
                        'account_type'          => 'Member',
                        'account_invite_code'   => $accountInviteCode,
                        'last_login'            => time(),
                        'login_ip'              => $loginIp,
                        'session_id'            => session_id(),
                    );
                
                    $accountId = self::$accountDb->createAccount($data);

                    $data = array(
                        'account_id'              => $accountId,
                        'account_address_type'    => 'billing',
                        'account_address_name'    => $values['account_shipping_address_name'],
                        'account_address_street'  => $values['account_shipping_address_street'],
                        'account_address_street2' => $values['account_shipping_address_street2'],
                        'account_address_city'    => $values['account_shipping_address_city'],
                        'account_address_postal'  => $values['account_shipping_address_postal'],
                        'account_address_zone'    => $values['account_shipping_address_zone'],    
                    );

                    $addressId = self::$accountDb->createAccountAddress($data);

                $this->_redirect('admin/account/index');
            }
        } else {
            $this->view->form = $form;
        }
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function editAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        } 

        $this->view->accountId = $accountId;

        $data = self::$accountDb->getAccount($accountId);

        // if empty data show error.
        if ( empty($data) ) {
            $this->render('account-not-found');
        }

        $form = self::$accountForm->adminEditForm($data);

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $data = array(
                    'account_email'         => $values['account_email'],
                    'account_firstname'     => $values['account_firstname'],
                    'account_lastname'      => $values['account_lastname'],
                    'account_telephone'     => $values['account_telephone'],
                    'account_alt_telephone' => $values['account_alt_telephone'],
                    'account_receive_email' => $values['account_receive_email'],
                    'account_email_type'    => $values['account_email_type'] 
                );    
                
                self::$accountDb->updateAccount($data, $accountId);

                $this->render('edit-complete');
            }
        } else {
           $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function suspendAction()
    {

        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        }         
    }


    /** 
     * @access Public
     * @return void
     */
    public function searchAction()
    {
        
    }


    /** 
     * @access Public
     * @return void
     */
    public function passwordAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        } 

        $form = self::$accountForm->adminPasswordForm($accountId);
        
        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function emailAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        } 

       
    }


    /** 
     * @access Public
     * @return void
     */
    public function addressAction()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');
        // no account id show account not found
        if ( $accountId < 1) {
            $this->render('account-not-found');
        } 

        // get billing address
        $billingAddress  = self::$accountDb->getAddress('billing', $accountId);

        // get shipping address
        $shippingAddress = self::$accountDb->getAddress('shipping', $accountId);

        $this->view->accountId = $accountId;

        $form = self::$accountForm->addressForm($billingAddress, $shippingAddress);

        // if post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
    
                $data = array(
                    'account_address_name'      => $values['account_shipping_address_name'],
                    'account_address_street'    => $values['account_shipping_address_street'],
                    'account_address_street2'   => $values['account_shipping_address_street2'],
                    'account_address_city'      => $values['account_shipping_address_city'],
                    'account_address_postal'    => $values['account_shipping_address_postal'],
                    'account_address_zone'      => $values['account_shipping_address_zone'],
                );
                self::$accountDb->updateAddress($data,'shipping',$accountId);

                $data = array(
                    'account_address_name'      => $values['account_billing_address_name'],
                    'account_address_street'    => $values['account_billing_address_street'],
                    'account_address_street2'   => $values['account_billing_address_street2'],
                    'account_address_city'      => $values['account_billing_address_city'],
                    'account_address_postal'    => $values['account_billing_address_postal'],
                    'account_address_zone'      => $values['account_billing_address_zone'],
                );
                self::$accountDb->updateAddress($data,'billing',$accountId);

                $this->render('address-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }

}

