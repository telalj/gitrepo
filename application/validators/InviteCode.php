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
 * @package    Core
 * @subpackage Validate
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: InviteCode.php 4 2009-6-1 Jaimie $
 */
class Zend_Validate_InviteCode extends Zend_Validate_Abstract
{
    /**
     * @var String
     */
    const EXISTS = 'exists';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::EXISTS => "The invite code %value% is not valid.",        
    );


    /**
     * @access Public
     * @param String $value
     * @param String $contex
     * @return Bool
     */
    public function isValid($value, $context = null)
    {
        $accountDb = new Model_Account_Db;

        if ($accountDb->validateCode($value) ) {  
            return true;
        } else {
            $this->_error(self::EXISTS);
            return false;          
        }

    }


    /**
     * @access Public
     * @return Bool
     */
    public function setFail()
    {
        $this->_error(self::EXISTS);
        return false;

    }

}
