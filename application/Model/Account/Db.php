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
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Account_Db
{
    /* @access Public
     * @var object
     */
    private static $db      = null;

    /* @access Public
     * @var object
     */
    private static $cache   = null;

    /* @access Public
     * @var object
     */
    private static $config  = null;


    /**
     * Class constructor
     * @access Public
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        $moduleConfig = Zend_Registry::get('moduleConfig');
        self::$config = $moduleConfig->module->account;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    
    /**
     * @access Public
     * @param Int $page
     * @return Object
     */
    public function getAccounts($page)
    {
        $selection = self::$db->select()->from("account");
        
        $paginator = Zend_Paginator::factory($selection);

        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage(self::$config->perPage);

        $paginator->setPageRange(self::$config->pageRange);

        return $paginator;
    }


    /**
     * @access Public
     * @param Int $accountId
     * @return Array
     */
    public function getAccount($accountId)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id', 'account_username', 
                'account_email', 'account_firstname', 'account_lastname','account_telephone', 
                'account_alt_telephone', 'account_receive_email', 'last_login', 
                'login_ip','account_email_type', 'account_invite_code', 'account_status', 'account_type') )
            ->where('a.account_id = ?', $accountId);

        $result  = self::$db->query($sql)->fetch();
    
        return $result;
    }


    /**
     * @access Public
     * @param String $addressType
     * @param Int $accountId
     * @return Array
     */
    public function getAddress($addressType, $accountId)
    {
       

        $sql = self::$db->select()
            ->from(array('ad' => 'account_address'), array('account_address_id','account_address_type','account_address_name','account_address_street',
                    'account_address_street2','account_address_city','account_address_postal','account_address_zone'))
            ->join(array('z' => 'zones'), 'ad.account_address_zone = z.zone_id ', array('zone_code','zone_name'))
            ->join(array('c' => 'countries'), 'z.zone_country_id = c.countries_id', array('countries_name','countries_iso_code_2','countries_iso_code_3') )
            ->where('ad.account_id = ?', $accountId)
            ->where('ad.account_address_type = ?', $addressType);
        
        $result  = self::$db->query($sql)->fetch();
        
        return $result;                 
    }


    /**
     * @access Public
     * @param String $code
     * @return Array
     */
    public function validateCode($code)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id'))
            ->where('a.account_invite_code = ?', $code)
            ->where('a.account_status = 1');

        $result = self::$db->query($sql)->fetch();

        if ($result['account_id'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }

    
    /**
     * @access Public
     * @param String $code
     * @return Bool
     */
    public function validateResetCode($code)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id'))
            ->where('a.account_invite_code = ?', $code)
            ->where('a.account_status = 2');

        $result = self::$db->query($sql)->fetch();

        if ($result['account_id'] > 0 ) {
            return true;
        } else {
            return false;
        }
    }
    

    /**
     * @access Public
     * @param String $email
     * @return Bool
     */
    public function validateEmail($email)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id'))
            ->where('a.account_email = ?', $email);

       $result  = self::$db->query($sql)->fetch();

        if ($result['account_id'] > 0 ) {
            return false;
        } else {
            return true;
        }
    }
    

    /**
     * @access Public
     * @param String $username
     * @return Bool
     */
    public function validateUsername($username)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id'))
            ->where('a.account_username = ?', $username);

       $result  = self::$db->query($sql)->fetch();

        if ($result['account_id'] > 0 ) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * @access Public
     * @param String $code
     * @return Array
     */
    public function getAccountByCode($code)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id', 'account_email','account_firstname','account_lastname','account_invite_code'))
            ->where('a.account_invite_code = ?', $code);
        
        $result  = self::$db->query($sql)->fetch();
        
        return $result;
    }


    /**
     * @access Public
     * @param String $email
     * @return Array
     */
    public function getAccountByEmail($email)
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('account_id', 'account_username', 'account_email','account_firstname','account_lastname','account_invite_code','account_email_type'))
            ->where('a.account_email = ?', $email);
        
        $result  = self::$db->query($sql)->fetch();
        
        return $result;
    }


    /**
     * @access Public
     * @return Array
     */
    public function getAccountCounts()
    {
        $sql = self::$db->select()
            ->from(array('a' => 'account'), array('count(account_id) as account_count'));
    
        $result  = self::$db->query($sql)->fetch();
        
        return $result['account_count'];
    }


    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function createAccount($data)
    {
        self::$db->insert('account', $data);
        return self::$db->lastInsertId();
    }

    
    /**
     * @access Public
     * @param Array $data
     * @return Int 
     */
    public function createAccountAddress($data)
    {
        self::$db->insert('account_address', $data);
        return self::$db->lastInsertId();
    }


    /** 
     * @access Public
     * @param Array $data
     * @param Int $account
     * @return Void
    */
    public function updateAccount($data, $accountId)
    {
        self::$db->update('account', $data, 'account_id = '. self::$db->quote($accountId));
    }


    /**
     * @access Public
     * @param Array $data
     * @param String $addressType
     * @param Int $accountId
     * @return Void
     */
    public function updateAddress($data, $addressType, $accountId)
    {
        self::$db->update('account_address', $data, 'account_id = '. self::$db->quote($accountId) . " AND account_address_type = " . self::$db->quote($addressType) );

    }

    
    /**
     * @access Public
     * @param Array $data
     * @return Int
     */
    public function setPlayStats($data)
    {
        self::$db->insert('statistics', $data);
        return self::$db->lastInsertId();
    }


    /**
     * @access Public
     * @param Int $length
     * @return String
     */
    public function getNewCode($length)
    {
        $code = md5(uniqid(rand(), true));
        if ($length != "") return substr($code, 0, $length);
        else return $code;
    }
    

    /**
     * @access Public
     * @return String
     */
    public function getVisitorIP()
    { 
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $TheIp=$_SERVER['HTTP_X_FORWARDED_FOR'];
        } else { 
            $TheIp=$_SERVER['REMOTE_ADDR'];
        }

        return trim($TheIp);
    }

}
