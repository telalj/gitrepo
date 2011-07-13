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
 * @package    Install
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: CreateAdminController.php 4 2009-6-1 Jaimie $
 */
class Install_CreateAdminController extends Zend_Controller_Action
{

    /* @access Public
     * @var object
     */
    private static $installDb   = null;

    /* @access Public
     * @var object
     */
    private static $installForm = null;

    /* @access Public
     * @var object
     */
    private static $installMail = null;

    /* @access Public
     * @var object
     */
    private static $accountDb   = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        self::$installForm = new Model_Install_Form;

        self::$installDb   = new Model_Install_Db;

        self::$accountDb   = new Model_Account_Db;

        $config = Zend_Registry::get('configuration');

        if($config->admin) {
            $this->_redirect('index');
        }

    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        
        $form = self::$installForm->createAdminForm();

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
                        'account_type'          => 'Administrator',
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

                    // set admin
                    $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini',
                        null,
                        array('skipExtends'        => true,
                        'allowModifications' => true));

                    $config->default->admin = 1;
                                
                    $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                        'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini'));

                    $writer->write();

                    $this->_redirect('install/complete');
            }
        } else {
            $this->view->form = $form;
        }
    }

}
