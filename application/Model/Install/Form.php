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
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Install_Form
{
    /* @access Public
     * @var object
     */
    private static $form         = null;

    /* @access Public
     * @var object
     */
    private static $translate    = null;

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
     * @return Object
     */
    public function dbForm()
    {
        self::$form->setAction('install/index')
            ->setMethod('post')
            ->setAttrib('id', 'install')
            ->setAttrib('name', 'install');

        // host
        $host = self::$form->createElement('text', 'host')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Install_Host_NotEmpty'))                       
            ->setLabel('Field_Install_Host')
            ->setAttrib('size', 20)
            ->setValue('localhost');

        // dbname
        $dbName = self::$form->createElement('text', 'dbname')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Install_Dbname_NotEmpty' ))
            ->setLabel('Field_Install_Dbname')
            ->setAttrib('size', 20)
            ->setValue('voodoo');

        // username
        $username = self::$form->createElement('text', 'username')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Install_Username_NotEmpty' ))
            ->setLabel('Field_Install_Username')
            ->setDescription('Field_Install_Username_Description')
            ->setAttrib('size', 20)
            ->setValue('root');

        // password
        $password = self::$form->createElement('text', 'password')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Install_Password_NotEmpty' ))
            ->setLabel('Field_Install_Password')
            ->setAttrib('size', 20);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Install_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form->addElement($host)
            ->addElement($dbName)
            ->addElement($username)
            ->addElement($password)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('host', 'dbname', 'username', 'password', 'no_csr', 'submit'), 'install', array('legend' => 'Form_Legend_Install_DB', ));
   
        return self::$form;   
    }


    /**
     * @access Public
     * @return Object
     */
    public function createAdminForm()
    {
        $geoObj   = new Model_Core_Address_Db;
        $geoZones = $geoObj->getGeoZones();

        self::$form->setAction('install/create-admin')
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

}
