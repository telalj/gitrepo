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
 * @version    $Id: RegisterController.php 4 2009-6-1 Jaimie $
 */
class Account_RegisterController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $accountType   = null;

    /* @access Public
     * @var object
     */
    private static $auth          = null;

    /* @access Public
     * @var object
     */
    private static $accountDb     = null;

    /* @access Public
     * @var object
     */
    private static $accountForm   = null;

    /* @access Public
     * @var object
     */
    private static $accountMail   = null;

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
        $registry = Zend_Registry::getInstance();

        self::$config = $registry->get('moduleConfig');

        if ( !self::$config->module->account->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Account');
        }

        self::$accountDb    = new Model_Account_Db;

        self::$accountForm  = new Model_Account_Form;

        self::$accountMail  = new Model_Account_Mail;

        self::$auth          = $this->view->auth;   
    }


    /** 
     * @access Public
     * @return Void
     */
    public function indexAction()
    {

        if(self::$auth->hasIdentity())
        {
            $this->_redirect('account/index');
        } else {
            
            // check what type of registration is set up
            if( self::$config->module->account->register == 'invite') {
                // we have invite re direct the user to the enter invite code
                $this->_redirect('account/invite');
            }

            // load form
            $form = self::$accountForm->registerForm();

            // if post
            if ($this->getRequest()->isPost()) {
                // If form is not valid 
                if (!$form->isValid($_POST)) {
                    $this->view->form = $form;
                } else {
                    $values = $form->getValues();

                    // if we require account email activate
                    if (self::$config->module->account->register == 'verify') {
                        $accountStatus = 1;
                    } else {
                        $accountStatus = 2;
                    }

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
                        'account_status'        => $accountStatus,
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

                    $_SESSION["account_id"] = $accountId;

                    $this->_redirect('account/register/complete');
                }
            } else {
                $this->view->form = $form;
            }            
        }
    }
    

    /** 
     * @access Public
     * @return Void
     */
    public function completeAction()
    {
        $accountId = $_SESSION["account_id"];

        if ($accountId < 1 ) {
            throw new exception('Missing required parameter:account_id');
        }

        // load account
        $accountContact = self::$accountDb->getAccount($accountId);
    
        $accountAddress = self::$accountDb->getAddress('billing', $accountId);

        $account = array_merge($accountContact, $accountAddress);

        // create email
        if ( self::$config->module->account->register == 'verify') {
            // send activation email
            $mail = self::$accountMail->sendActivationEmail($account);
        } else {
            // send normal registration email
            $mail = self::$accountMail->sendRegistrationEmail($account); 
        }
        
        // send admin email
        self::$accountMail->sendAdminRegistrationEmail($account);

        // display thank you
        if ( self::$config->module->account->register == 'verify') {
            $this->view->displayActivate = true;
        }
    }

}
