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
 * @version    $Id: LostPasswordController.php 4 2009-6-1 Jaimie $
 */
class Account_LostPasswordController extends Zend_Controller_Action
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

        self::$auth         = $this->view->auth;   
    }


    /** 
     * @access Public
     * @return Void
     */
    public function indexAction()
    {

        $form = self::$accountForm->lostPasswordForm();

        // if post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                // get account by email
                $account = self::$accountDb->getAccountByEmail($values['account_email']);

                if ( $account['account_id'] > 1) {
                    throw new exception('Missing account');
                }

                $accountInviteCode = self::$accountDb->getNewCode(16);

                // reset code
                $data = array(
                    'account_invite_code' => $accountInviteCode
                );
                self::$accountDb->updateAccount($data,$account['account_id']);
                
                $account['account_invite_code'] = $accountInviteCode;

                // email account        
                $mail = self::$accountMail->sendLostPassword($account);

                $this->_redirect('account/lost-password/sent');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return Void
     */
    public function sentAction()
    {


    }


    /** 
     * @access Public
     * @return Void
     */
    public function resetAction()
    {
        $code = $this->getRequest()->getParam('code');

        $form = self::$accountForm->passwordRestForm($code);

        // if post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                // load account by code
                $account = self::$accountDb->getAccountByCode($values['account_invite_code']);

                if ($account['account_id'] < 1 ) {
                    throw new exception('Missing Account');
                }

                $data = array(
                    'account_password'   => md5($values['account_password']),
                    'account_invite_code' => 'NULL'
                );

                self::$accountDb->updateAccount($data,$account['account_id']);

                $this->_redirect('account/lost-password/complete');
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
