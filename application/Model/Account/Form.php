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
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Account_Form
{
    /* @access Public
     * @var object
     */
    private static $form         = null;

    /* @access Public
     * @var object
     */
    private static $translate    = null;

    /* @access Public
     * @var object
     */
    private static $accountDb    = null; 

    /** Contructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$form = new Zend_Form();

        self::$translate = Zend_Registry::get('Zend_Translate');
    
        self::$form->setTranslator(self::$translate);

        self::$form->addPrefixPath('Element', 'Helper/Element/', 'element');
    }


    /**
     * @access Public
     * @param String $from
     * @return Object
     */
    public function LoginForm($from)
    {       
        self::$form->setAction('account/login')
            ->setMethod('post')
            ->setAttrib('id', 'login')
            ->setAttrib('name', 'login');

        // username
        $accountUsername = self::$form->createElement('text', 'account_username')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Missing_Username_Field' ))
            ->setLabel('Field_Account_Username')
            ->setAttrib('size', 20);
        
        // password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Missing_Password_Field' ))
            ->setLabel('Field_Account_Password') 
            ->setAttrib('size', 20);
        
        $accountFrom = self::$form->createElement('hidden', 'from')                 
            ->setValue($from);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Account_Login_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form->addElement($accountUsername)
            ->addElement($accountPassword)
            ->addElement($accountFrom)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('account_username', 'account_password', 'from', 'no_csr', 'submit'), 'login', array('legend' => 'Form_Legend_Account_Login', ));
   
        return self::$form;
	}

    
    /**
     * @access Public
     * @return Object
     */
    public function registerForm()
    {        
        $geoObj   = new Model_Core_Address_Db;
        $geoZones = $geoObj->getGeoZones();

        self::$form->setAction('account/register')
            ->setMethod('post')
            ->setAttrib('id', 'login')
            ->setAttrib('name', 'login');
            
        // account_firstname
        $accountFirstname = self::$form->createElement('text', 'account_firstname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Firstname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Firstname_StringLength'))
            ->setLabel('Field_Account_Firstname');

        // account_lastname
        $accountLastname = self::$form->createElement('text', 'account_lastname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Lastname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Lastname_StringLength'))
            ->setLabel('Field_Account_Lastname');

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->addValidator('EmailExists', true, array('messages' => 'Error_Account_Email_Address_EmailExists'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Email_Descrp');

        // account_telephone
        $accountTelephone = self::$form->createElement('text', 'account_telephone')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Telephone_NotEmpty'))
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Telephone_StringLength'))
            ->setLabel('Field_Account_Telephone');

        // account_alt_telephone
        $accountAltTelephone = self::$form->createElement('text', 'account_alt_telephone')
            ->setLabel('Field_Account_Alt_Telephone')
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Alt_Telephone_StringLength'));

        // account_receive_email
        $accountReceiveEmail = self::$form->createElement('checkbox', 'account_receive_email')
            ->setLabel('Field_Account_Receive_Email')
            ->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setDescription('Field_Account_Receive_Email_Descrp');

        // account_email_type
        $accountEmailType = self::$form->createElement('select', 'account_email_type')
            ->setLabel('Field_Account_Email_Type')
            ->setDescription('Field_Account_Email_Type_Descrp')
            ->addMultiOption('text', 'Field_Account_Email_Type_Text')
            ->addMultiOption('html', 'Field_Account_Email_Type_HTML');        
        
        // account_address_name
        $accountShippingAddressName = self::$form->createElement('text', 'account_shipping_address_name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Name_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Name_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Name')
            ->setAttrib('size', 40);

        // account_address_street
        $accountShippingAddressStreet = self::$form->createElement('text', 'account_shipping_address_street')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Street_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Street_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Street')
            ->setAttrib('size', 40);

        // account_address_street2
        $accountShippingAddressStreet2 = self::$form->createElement('text', 'account_shipping_address_street2')
            ->setLabel('Field_Account_Shipping_Address_Street2')
            ->setAttrib('size', 40);

        // account_address_city
        $accountShippingAddressCity = self::$form->createElement('text', 'account_shipping_address_city')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_City_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_City_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_City');

        // account_address_postal
        $accountShippingAddressPostal = self::$form->createElement('text', 'account_shipping_address_postal')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Postal_NotEmpty'))
            ->addValidator('StringLength', true, array(5, 20, 'messages' => 'Error_Account_Shipping_Address_Postal_StringLength'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Shipping_Address_Postal_Alnum'))
            ->setLabel('Field_Account_Shipping_Address_Postal')
            ->setAttrib('size', 10);

        // account_shiping_address_zone
        $accountShipingAddressZone = self::$form->createElement('select', 'account_shipping_address_zone')
            ->setLabel('Field_Account_Geo_Zone')
            ->setRequired(true);
        foreach ( $geoZones as $zone ) {
            $accountShipingAddressZone->addMultiOption($zone['zone_id'], $zone['countries_iso_code_3']. ' - ' . $zone['zone_name']);
        }

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Vfry_Password_NotEmpty'))
            ->addValidator('IdenticalField', true, array('account_password', 'Error_Account_Vfry_Password_IdenticalField'))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);

        // Username
        $accountUsername = self::$form->createElement('text', 'account_username')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('UsernameExists', true, array('messages' => 'Error_Account_Username_UsernameExists'))
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Username_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Username_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Username_StringLength'))
            ->setLabel('Field_Account_Username');

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Register_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form->addElement($accountFirstname)
            ->addElement($accountLastname)
            ->addElement($accountEmail)
            ->addElement($accountTelephone)
            ->addElement($accountAltTelephone)
            ->addElement($accountReceiveEmail)
            ->addElement($accountEmailType)
            ->addElement($accountShippingAddressName)
            ->addElement($accountShippingAddressStreet)
            ->addElement($accountShippingAddressStreet2)
            ->addElement($accountShippingAddressCity)
            ->addElement($accountShippingAddressPostal)
            ->addElement($accountShipingAddressZone)
            ->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($accountUsername)            
            ->addElement($submit)
            ->addElement($csr);

        self::$form->addDisplayGroup(array('account_firstname', 'account_lastname', 'account_email'), 'account_personal', array('legend' => 'Form_Legend_Account_Personal', ));

        self::$form->addDisplayGroup(array('account_telephone', 'account_alt_telephone' ), 'account_phone', array('legend' => 'Form_Legend_Account_Phone', ));

        self::$form->addDisplayGroup(array('account_shipping_address_name', 'account_shipping_address_street', 'account_shipping_address_street2', 'account_shipping_address_city', 'account_shipping_address_postal',  'account_shipping_address_zone'), 'account_Address', array('legend' => 'Form_Legend_Account_Address' ));
    
        self::$form->addDisplayGroup(array('account_username', 'account_password','vfry_account_password'), 'account_login', array('legend' => 'Form_Legend_Account_Login'));

        self::$form->addDisplayGroup(array('account_receive_email', 'account_email_type', 'no_csr', 'submit'), 'account_news_letters', array('legend' => 'Form_Legend_Account_News_Letters', ));


        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function inviteRegisterForm($data)
    {        
        $geoObj   = new Model_Core_Address_Db;
        $geoZones = $geoObj->getGeoZones();

        self::$form->setAction('account/invite/register/code/'.$data['account_invite_code'])
            ->setMethod('post')
            ->setAttrib('id', 'invite_register')
            ->setAttrib('name', 'invite_register');
            
        // account_firstname
        $accountFirstname = self::$form->createElement('text', 'account_firstname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Firstname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Firstname_StringLength'))
            ->setLabel('Field_Account_Firstname')
            ->setValue($data['account_firstname']);

        // account_lastname
        $accountLastname = self::$form->createElement('text', 'account_lastname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Lastname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Lastname_StringLength'))
            ->setLabel('Field_Account_Lastname')
            ->setValue($data['account_lastname']);

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->addValidator('EmailExists', true, array('messages' => 'Error_Account_Email_Address_EmailExists'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Email_Descrp')
            ->setValue($data['account_email']);

        // account_telephone
        $accountTelephone = self::$form->createElement('text', 'account_telephone')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Telephone_NotEmpty'))
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Telephone_StringLength'))
            ->setLabel('Field_Account_Telephone');

        // account_alt_telephone
        $accountAltTelephone = self::$form->createElement('text', 'account_alt_telephone')
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Alt_Telephone_StringLength'))
            ->setLabel('Field_Account_Alt_Telephone');

        // account_receive_email
        $accountReceiveEmail = self::$form->createElement('checkbox', 'account_receive_email')
            ->setLabel('Field_Account_Receive_Email')
            ->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setDescription('Field_Account_Receive_Email_Descrp');

        // account_email_type
        $accountEmailType = self::$form->createElement('select', 'account_email_type')
            ->setLabel('Field_Account_Email_Type')
            ->setDescription('Field_Account_Email_Type_Descrp')
            ->addMultiOption('text', 'Field_Account_Email_Type_Text')
            ->addMultiOption('html', 'Field_Account_Email_Type_HTML');        
        
        // account_address_name
        $accountShippingAddressName = self::$form->createElement('text', 'account_shipping_address_name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Name_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Name_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Name')
            ->setAttrib('size', 40);

        // account_address_street
        $accountShippingAddressStreet = self::$form->createElement('text', 'account_shipping_address_street')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Street_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Street_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Street')
            ->setAttrib('size', 40);

        // account_address_street2
        $accountShippingAddressStreet2 = self::$form->createElement('text', 'account_shipping_address_street2')
            ->setLabel('Field_Account_Shipping_Address_Street2')
            ->setAttrib('size', 40);

        // account_address_city
        $accountShippingAddressCity = self::$form->createElement('text', 'account_shipping_address_city')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_City_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_City_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_City');

        // account_address_postal
        $accountShippingAddressPostal = self::$form->createElement('text', 'account_shipping_address_postal')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Postal_NotEmpty'))
            ->addValidator('StringLength', true, array(5, 20, 'messages' => 'Error_Account_Shipping_Address_Postal_StringLength'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Shipping_Address_Postal_Alnum'))
            ->setLabel('Field_Account_Shipping_Address_Postal')
            ->setAttrib('size', 10);

        // account_shiping_address_zone
        $accountShipingAddressZone = self::$form->createElement('select', 'account_shipping_address_zone')
            ->setLabel('Field_Account_Geo_Zone')
            ->setRequired(true);
        foreach ( $geoZones as $zone ) {
            $accountShipingAddressZone->addMultiOption($zone['zone_id'], $zone['countries_iso_code_3']. ' - ' . $zone['zone_name']);
        }

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Vfry_Password_NotEmpty'))
            ->addValidator('IdenticalField', true, array('account_password', 'Error_Account_Vfry_Password_IdenticalField'))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);

        // Username
        $accountUsername = self::$form->createElement('text', 'account_username')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('UsernameExists', true, array('messages' => 'Error_Account_Username_UsernameExists'))
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Username_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Username_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Username_StringLength'))
            ->setLabel('Field_Account_Username');

        // account_id
        $accountId = self::$form->createElement('hidden', 'account_id')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_id', 'messages' => 'Error_Account_Missing_Account_Id_Field'))
            ->setValue($data['account_id']);

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Register_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form->addElement($accountFirstname)
            ->addElement($accountLastname)
            ->addElement($accountEmail)
            ->addElement($accountTelephone)
            ->addElement($accountAltTelephone)
            ->addElement($accountReceiveEmail)
            ->addElement($accountEmailType)
            ->addElement($accountShippingAddressName)
            ->addElement($accountShippingAddressStreet)
            ->addElement($accountShippingAddressStreet2)
            ->addElement($accountShippingAddressCity)
            ->addElement($accountShippingAddressPostal)
            ->addElement($accountShipingAddressZone)
            ->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($accountUsername)
            ->addElement($accountId)            
            ->addElement($submit)
            ->addElement($csr);

        self::$form->addDisplayGroup(array('account_firstname', 'account_lastname', 'account_email'), 'account_personal', array('legend' => 'Form_Legend_Account_Personal', ));

        self::$form->addDisplayGroup(array('account_telephone', 'account_alt_telephone' ), 'account_phone', array('legend' => 'Form_Legend_Account_Phone', ));

        self::$form->addDisplayGroup(array('account_shipping_address_name', 'account_shipping_address_street', 'account_shipping_address_street2', 'account_shipping_address_city', 'account_shipping_address_postal',  'account_shipping_address_zone'), 'account_Address', array('legend' => 'Form_Legend_Account_Address' ));
    
        self::$form->addDisplayGroup(array('account_username', 'account_password','vfry_account_password'), 'account_login', array('legend' => 'Form_Legend_Account_Login'));

        self::$form->addDisplayGroup(array('account_receive_email', 'account_email_type', 'account_id', 'no_csr', 'submit'), 'account_news_letters', array('legend' => 'Form_Legend_Account_News_Letters', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param String $code
     * @return Object
     */
    public function inviteActivateForm($code)
    {
        self::$form->setAction('account/invite')
            ->setMethod('post')
            ->setAttrib('id', 'invite')
            ->setAttrib('name', 'invite');

        // invite code
        $accountInviteCode = self::$form->createElement('text', 'account_invite_code')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Invite_Code_NotEmpty'))
            ->addValidator('InviteCode', true, array('account_invite_code', 'Error_Account_Invite_Code_InviteCode'))
            ->setLabel('Field_Account_Invite_Code')
            ->setAttrib('size', 40)
            ->setValue($code);

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Invite_Activate_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form
            ->addElement($accountInviteCode)
            ->addElement($csr)
            ->addElement($submit);
        
        self::$form->addDisplayGroup(array('account_invite_code', 'no_csr', 'submit'), 'account_invite', array('legend' => 'Form_Legend_Account_Activate_Invite', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param String $code
     * @return Object
     */
    public function activateForm($code)
    {
        self::$form->setAction('account/activate/index/code/')
            ->setMethod('post')
            ->setAttrib('id', 'activate')
            ->setAttrib('name', 'activate');

        // invite code
        $accountInviteCode = self::$form->createElement('text', 'account_invite_code')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Invite_Code_NotEmpty'))
            ->addValidator('InviteCode', true, array('account_invite_code', 'Error_Account_Invite_Code_InviteCode'))
            ->setLabel('Field_Account_Invite_Code')
            ->setAttrib('size', 40)
            ->setValue($code);

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Invite_Activate_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form
            ->addElement($accountInviteCode)
            ->addElement($csr)
            ->addElement($submit);
        
        self::$form->addDisplayGroup(array('account_invite_code', 'no_csr', 'submit'), 'account_invite', array('legend' => 'Form_Legend_Account_Activate', ));


        return self::$form;
    }
    
    
    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function editForm($data)
    {
        self::$form->setAction('account/edit')
            ->setMethod('post')
            ->setAttrib('id', 'edit')
            ->setAttrib('name', 'edit');

        // account_firstname
        $accountFirstname = self::$form->createElement('text', 'account_firstname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Firstname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Firstname_StringLength'))
            ->setLabel('Field_Account_Firstname')       
            ->setValue($data['account_firstname']);

        // account_lastname
        $accountLastname = self::$form->createElement('text', 'account_lastname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Lastname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Lastname_StringLength'))
            ->setLabel('Field_Account_Lastname')
            ->setValue($data['account_lastname']);

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Email_Descrp')
            ->setValue($data['account_email']);

        // account_telephone
        $accountTelephone = self::$form->createElement('text', 'account_telephone')
            ->setRequired(true)
            ->setLabel('Field_Account_Telephone')
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Telephone_NotEmpty'))
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Telephone_StringLength'))
            ->setValue($data['account_telephone']);

        // account_alt_telephone
        $accountAltTelephone = self::$form->createElement('text', 'account_alt_telephone')
            ->setLabel('Field_Account_Alt_Telephone')
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Alt_Telephone_StringLength'))
            ->setValue($data['account_alt_telephone']);        

        // account_receive_email
        $accountReceiveEmail = self::$form->createElement('checkbox', 'account_receive_email')
            ->setLabel('Field_Account_Receive_Email')
            ->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setDescription('Field_Account_Receive_Email_Descrp')
            ->setValue($data['account_receive_email']);

        // account_email_type
        $accountEmailType = self::$form->createElement('select', 'account_email_type')
            ->setLabel('Field_Account_Email_Type')
            ->setDescription('Field_Account_Email_Type_Descrp')
            ->addMultiOption('text', 'Field_Account_Email_Type_Text')
            ->addMultiOption('html', 'Field_Account_Email_Type_HTML')
            ->setValue($data['account_email_type']);


        // Submit
        $submit = self::$form->createElement('submit', 'submit')    
		    ->setLabel('Field_Account_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form->addElement($accountFirstname)
            ->addElement($accountLastname)
            ->addElement($accountEmail)
            ->addElement($accountTelephone)
            ->addElement($accountAltTelephone)
            ->addElement($accountReceiveEmail)
            ->addElement($accountEmailType)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('account_firstname', 'account_lastname', 'account_email'), 'edit_account_personal', array('legend' => 'Form_Legend_Account_Personal', ));

        self::$form->addDisplayGroup(array('account_telephone', 'account_alt_telephone', ), 'edit_account_phone', array('legend' => 'Form_Legend_Account_Phone', ));
    
        self::$form->addDisplayGroup(array('account_receive_email', 'account_email_type', 'no_csr', 'submit'), 'edit_account_email', array('legend' => 'Form_Legend_Account_Email', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function addressForm($data)
    {
        $geoObj   = new Model_Core_Address_Db;
        $geoZones = $geoObj->getGeoZones();

        self::$form->setAction('account/address')
            ->setMethod('post')
            ->setAttrib('id', 'address')
            ->setAttrib('name', 'address');

        // account_shipping_address_name
        $accountAddressName = self::$form->createElement('text', 'account_address_name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Name_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Name_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Name')
            ->setValue($data['account_address_name'])
            ->setAttrib('size', 40);

        // account_shipping_address_street
        $accountAddressStreet = self::$form->createElement('text', 'account_address_street')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Street_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Street_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Street')
            ->setValue($data['account_address_street'])
            ->setAttrib('size', 40);

        // account_shipping_address_street2
        $accountAddressStreet2 = self::$form->createElement('text', 'account_address_street2')
            ->setLabel('Field_Account_Shipping_Address_Street2')
            ->setValue($data['account_address_street2'])
            ->setAttrib('size', 40);

        // account_shipping_address_city
        $accountAddressCity = self::$form->createElement('text', 'account_address_city')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_City_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_City_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_City')
            ->setValue($data['account_address_city']);

        // account_shipping_address_postal
        $accountAddressPostal = self::$form->createElement('text', 'account_address_postal')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Postal_NotEmpty'))
            ->addValidator('StringLength', true, array(5, 20, 'messages' => 'Error_Account_Shipping_Address_Postal_StringLength'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Shipping_Address_Postal_Alnum'))
            ->setLabel('Field_Account_Shipping_Address_Postal')
            ->setValue($data['account_address_postal'])
            ->setAttrib('size', 10);

        // account_shiping_address_zone
        $accountAddressZone = self::$form->createElement('select', 'account_address_zone')
            ->setLabel('Field_Account_Geo_Zone')
            ->setRequired(true);
        foreach ( $geoZones as $zone ) {
            $accountAddressZone->addMultiOption($zone['zone_id'], $zone['countries_iso_code_3']. ' - ' . $zone['zone_name']);
        }
        $accountAddressZone->setValue($data['account_address_zone']);   

         // Submit
        $submit = self::$form->createElement('submit', 'submit')
            ->setLabel('Field_Account_Address_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form->addElement($accountAddressName)
            ->addElement($accountAddressStreet)            
            ->addElement($accountAddressStreet2)
            ->addElement($accountAddressCity)
            ->addElement($accountAddressPostal)
            ->addElement($accountAddressZone)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('account_address_name','account_address_street','account_address_street2','account_address_city','account_address_postal','account_address_zone','no_csr','submit'), 'address', array('legend' => 'Form_Legend_Account_Address', ));

        return self::$form;
    }


    /**
     * @access Public
     * @return Object
     */
    public function passwordForm()
    {
        self::$form->setAction('account/password')
            ->setMethod('post')
            ->setAttrib('id', 'edit_password')
            ->setAttrib('name', 'edit_password');

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Vfry_Password_NotEmpty'))
            ->addValidator('IdenticalField', true, array('account_password', 'Error_Account_Vfry_Password_IdenticalField'))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);

        // Submit
        $submit = self::$form->createElement('submit', 'submit')
            ->setAttrib('class','formSubmit')    
		    ->setLabel('Field_Account_Password_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($submit)
            ->addElement($csr);
        
        self::$form->addDisplayGroup(array('account_password', 'vfry_account_password', 'no_csr', 'submit'), 'edit_password', array('legend' => 'Form_Legend_Account_Change_Password', ));

        return self::$form;
    }


    /**
     * @access Public
     * @return Object
     */
    public function lostPasswordForm()
    {
        self::$form->setAction('account/lost-password/')
            ->setMethod('post')
            ->setAttrib('id', 'lost_password')
            ->setAttrib('name', 'lost_password');

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->addValidator('ValidEmail', true, array('messages' => 'Error_Account_Email_Address_ValidEmail'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Lost_Email_Descrp');


        // Submit
        $submit = self::$form->createElement('submit', 'submit')
            ->setAttrib('class','formSubmit')    
		    ->setLabel('Field_Account_Lost_Password_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form
            ->addElement($accountEmail)
            ->addElement($submit)
            ->addElement($csr);

        self::$form->addDisplayGroup(array('account_email', 'no_csr', 'submit'), 'login', array('legend' => 'Form_Legend_Account_Lost_Password', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param String $code
     * @return Object
     */
    public function passwordRestForm($code)
    {
        self::$form->setAction('account/lost-password/reset/code/')
            ->setMethod('post')
            ->setAttrib('id', 'reset_password')
            ->setAttrib('name', 'reset_password');

        // invite code
        $accountInviteCode = self::$form->createElement('text', 'account_invite_code')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_Code_NotEmpty'))
            ->addValidator('ResetCode', true, array('account_invite_code', 'Error_Account_Password_Code_ResetCode'))
            ->setLabel('Field_Account_Password_Code')
            ->setAttrib('size', 40)
            ->setValue($code);

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Vfry_Password_NotEmpty'))
            ->addValidator('IdenticalField', true, array('account_password', 'Error_Account_Vfry_Password_IdenticalField'))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);        

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Invite_Activate_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form
            ->addElement($accountInviteCode)
            ->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($csr)
            ->addElement($submit);
        
        self::$form->addDisplayGroup(array('account_invite_code', 'account_password', 'vfry_account_password', 'no_csr', 'submit'), 'account_invite', array('legend' => 'Form_Legend_Account_Reset_Password', ));


        return self::$form;
    }

   
    /**
     * @access Public
     * @return Object
     */  
    public function adminRegisterForm()
    {        
        $geoObj   = new Model_Core_Address_Db;
        $geoZones = $geoObj->getGeoZones();

        self::$form->setAction('admin/account/add')
            ->setMethod('post')
            ->setAttrib('id', 'login')
            ->setAttrib('name', 'login');
            
        // account_firstname
        $accountFirstname = self::$form->createElement('text', 'account_firstname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Firstname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Firstname_StringLength'))
            ->setLabel('Field_Account_Firstname');

        // account_lastname
        $accountLastname = self::$form->createElement('text', 'account_lastname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Lastname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Lastname_StringLength'))
            ->setLabel('Field_Account_Lastname');

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->addValidator('EmailExists', true, array('messages' => 'Error_Account_Email_Address_EmailExists'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Email_Descrp');

        // account_telephone
        $accountTelephone = self::$form->createElement('text', 'account_telephone')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Telephone_NotEmpty'))
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Telephone_StringLength'))
            ->setLabel('Field_Account_Telephone');

        // account_alt_telephone
        $accountAltTelephone = self::$form->createElement('text', 'account_alt_telephone')
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Alt_Telephone_StringLength'))
            ->setLabel('Field_Account_Alt_Telephone');

        // account_receive_email
        $accountReceiveEmail = self::$form->createElement('checkbox', 'account_receive_email')
            ->setLabel('Field_Account_Receive_Email')
            ->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setDescription('Field_Account_Receive_Email_Descrp');

        // account_email_type
        $accountEmailType = self::$form->createElement('select', 'account_email_type')
            ->setLabel('Field_Account_Email_Type')
            ->setDescription('Field_Account_Email_Type_Descrp')
            ->addMultiOption('text', 'Field_Account_Email_Type_Text')
            ->addMultiOption('html', 'Field_Account_Email_Type_HTML');        
        
        // account_address_name
        $accountShippingAddressName = self::$form->createElement('text', 'account_shipping_address_name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Name_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Name_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Name')
            ->setAttrib('size', 40);

        // account_address_street
        $accountShippingAddressStreet = self::$form->createElement('text', 'account_shipping_address_street')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Street_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_Street_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_Street')
            ->setAttrib('size', 40);

        // account_address_street2
        $accountShippingAddressStreet2 = self::$form->createElement('text', 'account_shipping_address_street2')
            ->setLabel('Field_Account_Shipping_Address_Street2')
            ->setAttrib('size', 40);

        // account_address_city
        $accountShippingAddressCity = self::$form->createElement('text', 'account_shipping_address_city')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_City_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Shipping_Address_City_StringLength'))
            ->setLabel('Field_Account_Shipping_Address_City');

        // account_address_postal
        $accountShippingAddressPostal = self::$form->createElement('text', 'account_shipping_address_postal')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Shipping_Address_Postal_NotEmpty'))
            ->addValidator('StringLength', true, array(5, 20, 'messages' => 'Error_Account_Shipping_Address_Postal_StringLength'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Shipping_Address_Postal_Alnum'))
            ->setLabel('Field_Account_Shipping_Address_Postal')
            ->setAttrib('size', 10);

        // account_shiping_address_zone
        $accountShipingAddressZone = self::$form->createElement('select', 'account_shipping_address_zone')
            ->setLabel('Field_Account_Geo_Zone')
            ->setRequired(true);
        foreach ( $geoZones as $zone ) {
            $accountShipingAddressZone->addMultiOption($zone['zone_id'], $zone['countries_iso_code_3']. ' - ' . $zone['zone_name']);
        }

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Vfry_Password_NotEmpty'))
            ->addValidator('IdenticalField', true, array('account_password', 'Error_Account_Vfry_Password_IdenticalField'))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);

        // Username
        $accountUsername = self::$form->createElement('text', 'account_username')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('UsernameExists', true, array('messages' => 'Error_Account_Username_UsernameExists'))
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Username_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Username_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Username_StringLength'))
            ->setLabel('Field_Account_Username');

        // Submit
        $submit = self::$form->createElement('submit', 'submit')       
		    ->setLabel('Field_Account_Register_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form->addElement($accountFirstname)
            ->addElement($accountLastname)
            ->addElement($accountEmail)
            ->addElement($accountTelephone)
            ->addElement($accountAltTelephone)
            ->addElement($accountReceiveEmail)
            ->addElement($accountEmailType)
            ->addElement($accountShippingAddressName)
            ->addElement($accountShippingAddressStreet)
            ->addElement($accountShippingAddressStreet2)
            ->addElement($accountShippingAddressCity)
            ->addElement($accountShippingAddressPostal)
            ->addElement($accountShipingAddressZone)
            ->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($accountUsername)            
            ->addElement($submit)
            ->addElement($csr);

        self::$form->addDisplayGroup(array('account_firstname', 'account_lastname', 'account_email'), 'account_personal', array('legend' => 'Form_Legend_Account_Personal', ));

        self::$form->addDisplayGroup(array('account_telephone', 'account_alt_telephone' ), 'account_phone', array('legend' => 'Form_Legend_Account_Phone', ));

        self::$form->addDisplayGroup(array('account_shipping_address_name', 'account_shipping_address_street', 'account_shipping_address_street2', 'account_shipping_address_city', 'account_shipping_address_postal',  'account_shipping_address_zone'), 'account_Address', array('legend' => 'Form_Legend_Account_Address' ));
    
        self::$form->addDisplayGroup(array('account_username', 'account_password','vfry_account_password'), 'account_login', array('legend' => 'Form_Legend_Account_Login'));

        self::$form->addDisplayGroup(array('account_receive_email', 'account_email_type', 'no_csr', 'submit'), 'account_news_letters', array('legend' => 'Form_Legend_Account_News_Letters', ));


        return self::$form;
    }

    
    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function adminEditForm($data)
    {
        self::$form->setAction('admin/account/edit/account_id/'.$data['account_id'])
            ->setMethod('post')
            ->setAttrib('id', 'admin_edit_account')
            ->setAttrib('name', 'admin_edit_account');

        // account_firstname
        $accountFirstname = self::$form->createElement('text', 'account_firstname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Firstname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Firstname_StringLength'))
            ->setLabel('Field_Account_Firstname')       
            ->setValue($data['account_firstname']);

        // account_lastname
        $accountLastname = self::$form->createElement('text', 'account_lastname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Lastname_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 60, 'messages' => 'Error_Account_Lastname_StringLength'))
            ->setLabel('Field_Account_Lastname')
            ->setValue($data['account_lastname']);

        // account_email
        $accountEmail = self::$form->createElement('text', 'account_email')
            ->setRequired(true)
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Email_Address_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Account_Email_Address_EmailAddress'))
            ->setLabel('Field_Account_Email_Address')
            ->setDescription('Field_Account_Email_Descrp')
            ->setValue($data['account_email']);

        // account_telephone
        $accountTelephone = self::$form->createElement('text', 'account_telephone')
            ->setRequired(true)
            ->setLabel('Field_Account_Telephone')
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Telephone_NotEmpty'))
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Telephone_StringLength'))
            ->setValue($data['account_telephone']);

        // account_alt_telephone
        $accountAltTelephone = self::$form->createElement('text', 'account_alt_telephone')
            ->setLabel('Field_Account_Alt_Telephone')
            ->addValidator('StringLength', true, array(10, 20, 'messages' => 'Error_Account_Alt_Telephone_StringLength'))
            ->setValue($data['account_alt_telephone']);        

        // account_receive_email
        $accountReceiveEmail = self::$form->createElement('checkbox', 'account_receive_email')
            ->setLabel('Field_Account_Receive_Email')
            ->setCheckedValue(1)
            ->setUncheckedValue(0)
            ->setDescription('Field_Account_Receive_Email_Descrp')
            ->setValue($data['account_receive_email']);

        // account_email_type
        $accountEmailType = self::$form->createElement('select', 'account_email_type')
            ->setLabel('Field_Account_Email_Type')
            ->setDescription('Field_Account_Email_Type_Descrp')
            ->addMultiOption('text', 'Field_Account_Email_Type_Text')
            ->addMultiOption('html', 'Field_Account_Email_Type_HTML')
            ->setValue($data['account_email_type']);

        // Submit
        $submit = self::$form->createElement('submit', 'submit')    
		    ->setLabel('Field_Account_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form->addElement($accountFirstname)
            ->addElement($accountLastname)
            ->addElement($accountEmail)
            ->addElement($accountTelephone)
            ->addElement($accountAltTelephone)
            ->addElement($accountReceiveEmail)
            ->addElement($accountEmailType)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('account_firstname', 'account_lastname', 'account_email'), 'edit_account_personal', array('legend' => 'Form_Legend_Account_Personal', ));

        self::$form->addDisplayGroup(array('account_telephone', 'account_alt_telephone', ), 'edit_account_phone', array('legend' => 'Form_Legend_Account_Phone', ));
    
        self::$form->addDisplayGroup(array('account_receive_email', 'account_email_type', 'no_csr', 'submit'), 'edit_account_email', array('legend' => 'Form_Legend_Account_Email', ));

        return self::$form;
    }

    
    /**
     * @access Public
     * @param Int $accountId
     * @return Object
     */
    public function adminPasswordForm($accountId)
    {
        self::$form->setAction('admin/account/password/account_id/' . $accountId)
            ->setMethod('post')
            ->setAttrib('id', 'admin_reset_password')
            ->setAttrib('name', 'admin_reset_password');

        // account_password
        $accountPassword = self::$form->createElement('password', 'account_password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Account_Password_NotEmpty'))
            ->addValidator('Alnum', true, array('messages' => 'Error_Account_Password_Alnum'))
            ->addValidator('StringLength', true, array(6, 24, 'messages' => 'Error_Account_Password_StringLength'))
            ->setLabel('Field_Account_Password')
            ->setAttrib('size', 20);

        // vfry_account_password
        $vfryAccountPassword = self::$form->createElement('password', 'vfry_account_password')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('account_password', 'messages' => 'Error_Account_Missing_Vfry_Password_Field' ))
            ->addValidator('IdenticalField', true, array('account_password', 'Field_Account_Vfry_Password_Field' ))
            ->setLabel('Field_Account_Vfry_Password')
            ->setAttrib('size', 20);

         // Submit
        $submit = self::$form->createElement('submit', 'submit')
            ->setAttrib('class','formSubmit')    
		    ->setLabel('Field_Account_Password_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));

        self::$form->addElement($accountPassword)
            ->addElement($vfryAccountPassword)
            ->addElement($submit)
            ->addElement($csr);
        
        self::$form->addDisplayGroup(array('account_password', 'vfry_account_password', 'no_csr', 'submit'), 'login', array('legend' => 'Form_Legend_Account_Reset_Password', ));

        return self::$form;
    }

   
    /**
     * @access Public
     * @return Object
     */ 
    public function emailForm()
    {

       self::$form->setAction('/account/email')
            ->setMethod('post')
            ->setAttrib('id', 'news_letters')
            ->setAttrib('name', 'news_letters');

         // Submit
        $submit = self::$form->createElement('submit', 'submit')        
		    ->setLabel('Field_Account_Email_Submit');

        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array('salt' => 'unique'));
        
        self::$form
            ->addElement($submit)
            ->addElement($csr);

        self::$form->addDisplayGroup(array( 'no_csr', 'submit'), 'edit_account_news_letters', array('legend' => 'Form_Legend_Account_News_Letters', ));

        return self::$form;
    }
}
