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
 * @version    $Id: ActivateController.php 4 2009-6-1 Jaimie $
 */
class Account_ActivateController extends Zend_Controller_Action
{
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
    private static $config        = null;


    /** 
     * @access Public
     * @return
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

        self::$auth          = $this->view->auth;    
    }


    /** 
     * @access Public
     * @return Void
     */
    public function indexAction()
    {
        $code = (string)$this->getRequest()->getParam('code');

        $form = self::$accountForm->activateForm($code);

         if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                // load account
                $account = self::$accountDb->getAccountByCode($values['account_invite_code']);
                
                $data = array(
                    'account_status'      => 2,
                    'account_invite_code' => '',
                );

                self::$accountDb->updateAccount($data,$account['account_id']);

                $this->_redirect('account/activate/complete');
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
