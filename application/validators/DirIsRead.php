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
 * @version    $Id: DirIsRead.php 4 2009-6-1 Jaimie $
 */
class  Zend_Validate_DirIsRead extends Zend_Validate_Abstract
{
    /**
     * @var String
     */
    const DIR = 'email';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::DIR => "Can not read the directory"
    );

    /**
     * @access Public
     * @param String $value
     * @return Bool
     */
    public function isValid($value)
    {
        
        $this->_setValue($value);

        if( is_dir($value) ){ 
            // is a dir can we access it
            if( is_readable($value) ) {
                return true;    
            } else {
                $this-> _error();
                return false;
            }      
        } else {
            $this->_error();
            return false;
        }

        $this->_error();
        return false;
    }
}
