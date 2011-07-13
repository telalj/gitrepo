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
 * @subpackage Soap
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Soap.php 4 2009-6-1 Jaimie $
 */
class Model_Account_Soap
{
    /* @access Public
     * @var object
     */
    private $_db;

    /** Contructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        Zend_Loader::loadClass('Zend_Cache');
        Zend_Loader::loadClass('Zend_Db');
        Zend_Loader::loadClass('Zend_Db_Table_Abstract');
        Zend_Loader::loadClass('Zend_Config_Ini');

        $config = new Zend_Config_Ini('config.ini', 'default');

        $db = Zend_Db::factory($config->database);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
        
        $this->_db = $db;      
    }

    
    /** 
     * @access Public
     * @return Array
    */    
    public function getActiveAccounts()
    {
        $sql = "SELECT *                 
                FROM account";
        $result = $this->_db->fetchAll($sql);
        return $result;
    }


    /** 
     * @access Public
     * @param Int $accountId
     * @return Array Account Fields
     */
    public function getAccount($accountId)
    {
        $sql = "SELECT * FROM account WHERE account_id = " . $this->_db->quote($accountId);
        $result = $this->_db->fetchRow($sql);
    
        return $result;
    }

    /**
     * @access public
     * @param String $accountUsername
     * @param String $accountPassword
     * @param String $accountEmail
     * @param String $accountFirstname
     * @param String $accountLastname
     * @param String $accountStatus
     * @param String $accountType
     * @return Int The account Id
    */
    public function addAccount($accountUsername, $accountPassword, $accountEmail, $accountFirstname, $accountLastname, $accountStatus, $accountType)
    {

    }

    
    /** 
     * @access Public
     * @param Int $accountId
     * @param String $accountAddressType
     * @param String $accountAddressName
     * @param String $accountAddressStreet
     * @param String $accountAddressStreet2
     * @param String $accountAddressCity
     * @param String $accountAddressProvince
     * @param String $accountAddressPostal
     * @param Int $accountAddressCountry
     * @param Int $accountAddressZone
     * @return Int Address Id
    */
    public function addAccontAddress($accountId, $accountAddressType, $accountAddressName, 
                                    $accountAddressStreet, $accountAddressStreet2, 
                                    $accountAddressCity, $accountAddressProvince, 
                                    $accountAddressPostal, $accountAddressCountry, 
                                    $accountAddressZone)
    {

    }


    /** set an account password
     * @access Public
     * @param Int $accountId
     * @param String $password
     * @return Bool
    */
    public function setPassword($accountId, $password)
    {

    }


    /** 
     * @access Public
     * @param String $username
     * @return Bool
    */
    public function isAvailable($username)
    {

    }
}
