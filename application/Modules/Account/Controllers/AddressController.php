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
 * @version    $Id: AddressController.php 4 2009-6-1 Jaimie $
 */
class Account_AddressController extends Zend_Controller_Action
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
    private static $accountType = null;

    /* @access Public
     * @var object
     */
    private static $accountDb   = null;

    /* @access Public
     * @var object
     */
    private static $accountForm = null;


    /** 
     * @access Public
     * @return Void
     */
    public function init()
    {       
        // load registry
        $registry = Zend_Registry::getInstance();
        
        // if module is active
        if ( !$registry->get('moduleConfig')->module->account->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Account');
        }

        self::$accountDb   = new Model_Account_Db;
        self::$accountForm = new Model_Account_Form;    
        self::$acl         = $this->view->acl;        
        self::$auth        = $this->view->auth;

        // No identity redirect to login page
        if(!self::$auth->hasIdentity()) {
            $this->_redirect('account/login/index/from/account:index');
        }        
    }


    /** 
     * @access Public
     * @return Void
     */
    public function indexAction()
    {
        // load addresses
        $address  = self::$accountDb->getAddress('billing',  self::$auth->getIdentity()->account_id);

        $form = self::$accountForm->addressForm($address);

        // if post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();
    
                $data = array(
                    'account_address_name'      => $values['account_address_name'],
                    'account_address_street'    => $values['account_address_street'],
                    'account_address_street2'   => $values['account_address_street2'],
                    'account_address_city'      => $values['account_address_city'],
                    'account_address_postal'    => $values['account_address_postal'],
                    'account_address_zone'      => $values['account_address_zone'],
                );
                self::$accountDb->updateAddress($data,'billing',self::$auth->getIdentity()->account_id);              
                
                $this->_redirect('account/address/complete');
            }
        } else {
            $this->view->form = $form;
        }

                 
    }


    /** 
     * @access Public
     * @return Void
     */
    public function completeAction()
    {
            
    }

}
