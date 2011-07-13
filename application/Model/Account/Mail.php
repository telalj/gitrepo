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
 * @subpackage Mail
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 4 2009-6-1 Jaimie $
 */
class Model_Account_Mail
{
    /* @access Public
     * @var object
     */
    private static $mail      = null;

    /* @access Public
     * @var object
     */
    private static $config    = null;

    /* @access Public
     * @var object
     */
    private static $translate = null;


    /**
     * Class constructor
     * @access Public
     */
    public function __construct()
    {
        self::$mail      = new  Zend_Mail(); 

        self::$config    = Zend_Registry::get('configuration');

        self::$translate = Zend_Registry::get('Zend_Translate');

        // set transport
        switch(self::$config->config->email->type)
        {
            case 'sendmail':
                self::$mail->setDefaultTransport(new Zend_Mail_Transport_Sendmail());
            break;

            case 'smtp':
                $tr = new Zend_Mail_Transport_Smtp(self::$config->config->email->host);
                self::$mail->setDefaultTransport($tr);
            break;
        }
       
        // set from
        self::$mail->setFrom(self::$config->config->email->from->email,self::$config->config->email->from->name);
    }


    /**
     * @access Public
     * param Array $account
     * @return void
     */
    public function sendActivationEmail($account)
    {
        self::$mail->addTo($account['account_email'], $account['account_username']);

        self::$mail->setSubject(self::$translate->translate('Mail_Account_Email_Activate_Subject'));

        $txtBody = self::$translate->translate('Mail_Account_Email_Activate_Welcome') . "\n";
        
        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Link') . "\n";
    
        $txtBody .= 'http://' . $_SERVER['HTTP_HOST'] . '/account/activate/index/code/' . $account['account_invite_code'] . "\n\n"; 

        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Contact_Details') . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Username') . ': ' . $account['account_username'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Firstname') . ': ' . $account['account_firstname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Lastname') . ': ' . $account['account_lastname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Telephone') . ': ' . $account['account_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Alt_Telephone') . ': ' . $account['account_alt_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Receive_Email') . ': ';

        if ($account['account_receive_email'] == 1) {
            $txtBody .= self::$translate->translate('Yes') . "\n";
        } else {
            $txtBody .= self::$translate->translate('No') . "\n";
        }

        $txtBody .= self::$translate->translate('Field_Account_Email_Type') . ': ' . $account['account_email_type'] . "\n";

        $txtBody .= "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Name') . ': ' . $account['account_address_name'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Street') . ': ' . $account['account_address_street'] . "\n";

        if ( !empty($account['account_address_street2']) ) {
            $txtBody .= self::$translate->translate('Field_Account_Address_Street2') . ': ' . $account['account_address_street2'] . "\n";
        }    

        $txtBody .= self::$translate->translate('Field_Account_Address_City') . ': ' . $account['account_address_city'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Postal') . ': ' . $account['account_address_postal'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Geo_Zone') . ': ' . $account['zone_name'] . ', '. $account['countries_iso_code_3'] . "\n\n";

        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Thankyou') . "\n";

        $txtBody .= 'http://' . $_SERVER['HTTP_HOST'];
                        
        self::$mail->setBodyText($txtBody);
   
        // if we have HTML Mail
        if ( $account['account_email_type'] == 'html' ) {            

            $htmlBody = '<h1>' . self::$translate->translate('Mail_Account_Email_Activate_Welcome') . '</h1>';
            
            $htmlBody .= '<p>' . self::$translate->translate('Mail_Account_Email_Activate_Link') . '<br>';
        
            $htmlBody .= '<a href="http://' . $_SERVER['HTTP_HOST'] . '/account/activate/index/code/' . $account['account_invite_code'] . '">http://' . $_SERVER['HTTP_HOST'] . '/account/activate/index/code/' . $account['account_invite_code'] . '</a>'; 

            $htmlBody .= '<h3>' . self::$translate->translate('Mail_Account_Email_Activate_Contact_Details') . '</h3>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Username') . ': </b>' . $account['account_username'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Firstname') . ': </b>' . $account['account_firstname'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Lastname') . ': </b>' . $account['account_lastname'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Telephone') . ': </b>' . $account['account_telephone'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Alt_Telephone') . ': </b>' . $account['account_alt_telephone'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Receive_Email') . ': </b>';

            if ($account['account_receive_email'] == 1) {
                $htmlBody .= self::$translate->translate('Yes') . '<br>';
            } else {
                $htmlBody .= self::$translate->translate('No') . '<br>';
            }

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Email_Type') . ': </b>' . $account['account_email_type'] . '<br>';

            $htmlBody .= '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Name') . ': </b>' . $account['account_address_name'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Street') . ': </b>' . $account['account_address_street'] . '<br>';

            if ( !empty($account['account_address_street2']) ) {
                $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Street2') . ': </b>' . $account['account_address_street2'] . '<br>';
            }    

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_City') . ': </b>' . $account['account_address_city'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Postal') . ': </b>' . $account['account_address_postal'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Geo_Zone') . ': </b>' . $account['zone_name'] . ', ' . $account['countries_iso_code_3'] . '<br><br>';

            $htmlBody .= '<p>' . self::$translate->translate('Mail_Account_Email_Activate_Thankyou') . '<br>';

            $htmlBody .= '<a href="http://' . $_SERVER['HTTP_HOST'] . '">http://' . $_SERVER['HTTP_HOST'] . '</a></p>';

            self::$mail->setBodyHtml($htmlBody);
        }

        self::_send();
    }


    /**
     * @access Public
     * @param Array $account
     * @return Void
     */
    public function sendRegistrationEmail($account)
    {
        self::$mail->addTo($account['account_email'], $account['account_username']);

        self::$mail->setSubject(self::$translate->translate('Mail_Account_Email_Register_Subject'));

        $txtBody = self::$translate->translate('Mail_Account_Email_Register_Welcome') . "\n";

        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Contact_Details') . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Username') . ': ' . $account['account_username'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Firstname') . ': ' . $account['account_firstname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Lastname') . ': ' . $account['account_lastname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Telephone') . ': ' . $account['account_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Alt_Telephone') . ': ' . $account['account_alt_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Receive_Email') . ': ';

        if ($account['account_receive_email'] == 1) {
            $txtBody .= self::$translate->translate('Yes') . "\n";
        } else {
            $txtBody .= self::$translate->translate('No') . "\n";
        }

        $txtBody .= self::$translate->translate('Field_Account_Email_Type') . ': ' . $account['account_email_type'] . "\n";

        $txtBody .= "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Name') . ': ' . $account['account_address_name'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Street') . ': ' . $account['account_address_street'] . "\n";

        if ( !empty($account['account_address_street2']) ) {
            $txtBody .= self::$translate->translate('Field_Account_Address_Street2') . ': ' . $account['account_address_street2'] . "\n";
        }    

        $txtBody .= self::$translate->translate('Field_Account_Address_City') . ': ' . $account['account_address_city'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Postal') . ': ' . $account['account_address_postal'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Geo_Zone') . ': ' . $account['zone_name'] . ', '. $account['countries_iso_code_3'] . "\n\n";

        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Thankyou') . "\n";

        $txtBody .= 'http://' . $_SERVER['HTTP_HOST'];
                        
        self::$mail->setBodyText($txtBody);
   
        // if we have HTML Mail
        if ( $account['account_email_type'] == 'html' ) {            

            $htmlBody = '<h1>' . self::$translate->translate('Mail_Account_Email_Register_Welcome') . '</h1>';
            
            $htmlBody .= '<h3>' . self::$translate->translate('Mail_Account_Email_Activate_Contact_Details') . '</h3>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Username') . ': </b>' . $account['account_username'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Firstname') . ': </b>' . $account['account_firstname'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Lastname') . ': </b>' . $account['account_lastname'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Telephone') . ': </b>' . $account['account_telephone'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Alt_Telephone') . ': </b>' . $account['account_alt_telephone'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Receive_Email') . ': </b>';

            if ($account['account_receive_email'] == 1) {
                $htmlBody .= self::$translate->translate('Yes') . '<br>';
            } else {
                $htmlBody .= self::$translate->translate('No') . '<br>';
            }

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Email_Type') . ': </b>' . $account['account_email_type'] . '<br>';

            $htmlBody .= '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Name') . ': </b>' . $account['account_address_name'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Street') . ': </b>' . $account['account_address_street'] . '<br>';

            if ( !empty($account['account_address_street2']) ) {
                $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Street2') . ': </b>' . $account['account_address_street2'] . '<br>';
            }    

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_City') . ': </b>' . $account['account_address_city'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Address_Postal') . ': </b>' . $account['account_address_postal'] . '<br>';

            $htmlBody .= '<b>' . self::$translate->translate('Field_Account_Geo_Zone') . ': </b>' . $account['zone_name'] . ', ' . $account['countries_iso_code_3'] . '<br><br>';

            $htmlBody .= '<p>' . self::$translate->translate('Mail_Account_Email_Activate_Thankyou') . '<br>';

            $htmlBody .= '<a href="http://' . $_SERVER['HTTP_HOST'] . '">http://' . $_SERVER['HTTP_HOST'] . '</a></p>';

            self::$mail->setBodyHtml($htmlBody);
        }

        self::_send();
    }


    /**
     * @access Public
     * @param Array $account
     * @return Void
     */
    public function sendLostPassword($account)
    {
        self::$mail->addTo($account['account_email'], $account['account_username']);

        self::$mail->setSubject(self::$translate->translate('Mail_Account_Email_Lost_Password_Subject'));

        $txtBody = self::$translate->translate('Mail_Account_Email_Lost_Password_Text') . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Username') . ': ' . $account['account_username'] . "\n"; 

        $txtBody .= 'http://' . $_SERVER['HTTP_HOST'] . '/account/lost-password/reset/code/' . $account['account_invite_code'] . "\n";         

        self::$mail->setBodyText($txtBody);

        if ( $account['account_email_type'] == 'html' ) {

            $htmlBody = '<p>' . self::$translate->translate('Mail_Account_Email_Lost_Password_Text') . '</p>';

            $htmlBody .= '<p><b>'.self::$translate->translate('Field_Account_Username') . ':</b> ' . $account['account_username'] . '</p>'; 

            $htmlBody .= '<a href="http://' . $_SERVER['HTTP_HOST'] . '/account/lost-password/reset/code/' . $account['account_invite_code'] .'">http://' . $_SERVER['HTTP_HOST'] . '/account/lost-password/reset/code/' . $account['account_invite_code'] . '</a>';  

            self::$mail->setBodyHtml($htmlBody);
        }

        self::_send();
    }


    /**
     * @access Public
     * @param Array $account
     * @return Void
     */
    public function sendAdminRegistrationEmail($account)
    {

        self::$mail->addTo(self::$config->config->email->admin, self::$config->config->email->admin);       

        $txtBody .= self::$translate->translate('Mail_Account_Email_Activate_Contact_Details') . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Username') . ': ' . $account['account_username'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Firstname') . ': ' . $account['account_firstname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Lastname') . ': ' . $account['account_lastname'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Telephone') . ': ' . $account['account_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Alt_Telephone') . ': ' . $account['account_alt_telephone'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Receive_Email') . ': ';

        if ($account['account_receive_email'] == 1) {
            $txtBody .= self::$translate->translate('Yes') . "\n";
        } else {
            $txtBody .= self::$translate->translate('No') . "\n";
        }

        $txtBody .= self::$translate->translate('Field_Account_Email_Type') . ': ' . $account['account_email_type'] . "\n";

        $txtBody .= "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Name') . ': ' . $account['account_address_name'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Street') . ': ' . $account['account_address_street'] . "\n";

        if ( !empty($account['account_address_street2']) ) {
            $txtBody .= self::$translate->translate('Field_Account_Address_Street2') . ': ' . $account['account_address_street2'] . "\n";
        }    

        $txtBody .= self::$translate->translate('Field_Account_Address_City') . ': ' . $account['account_address_city'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Address_Postal') . ': ' . $account['account_address_postal'] . "\n";

        $txtBody .= self::$translate->translate('Field_Account_Geo_Zone') . ': ' . $account['zone_name'] . ', '. $account['countries_iso_code_3'] . "\n\n";

        $txtBody .= 'http://' . $_SERVER['HTTP_HOST'];
                        
        self::$mail->setBodyText($txtBody);

        self::_send();
    }


    /**
     * @access Private
     * @return Void
     */
    private function _send()
    {
        self::$mail->send();

    }
}

