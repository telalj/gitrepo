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
 * @version    $Id: EmailExists.php 4 2009-6-1 Jaimie $
 */
class  Zend_Validate_EmailExists extends Zend_Validate_Abstract
{
    /**
     * @var String
     */
    const EMAIL = 'email';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::EMAIL => "'%value%' has already registered on this site. Please provide a different email address."
    );

    /**
     * @access Public
     * @param String $value
     * @return Bool
     */
    public function isValid($value)
    {
        
        $this->_setValue($value);

        $accountDb = new Model_Account_Db;

        if (!$accountDb->validateEmail($value))
        {
            $this->_error();
            return false;
        } else {
            return true;
        }

        
    }
}
