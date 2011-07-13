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
 * @version    $Id: Writable.php 4 2009-6-1 Jaimie $
 */
class Zend_Validate_Writable extends Zend_Validate_Abstract
{
    
    /**
     * @var String
     */
    const DIRWRITE = 'dirwrite';

    /**
     * @var Array
     */
    protected $_messageTemplates = array(
        self::DIRWRITE => "The content directory '%value%' is not writable.",        
    );

    /**
     * @var Array
     */
    protected $_messageVariables = array(
        'dir' => '_dir'
    );

    /**
     * @access Public
     * @param String $dir
     */
    public function __construct($dir = null)
    {
        $this->setDir($dir);       
    }


    /**
     * @access Public
     * @param String $dir
     * @return Object
     */
    public function setDir($dir)
    {

        if (strlen($dir) < 1) {
        require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The directory must be passed");
        }

        $this->_dir = $dir;
        return $this;
    }



    /**
     * @access Public
     * @return String
     */
    public static function getDir()
    {
        return $this->_dir;
    }



    /**
     * @access Public
     * @param String $value
     * @return Bool
     */
    public function isValid($value, $context = null)
    {

        if ( is_writable( $this->_dir ) ) {  
            return true;
        } else {
            $this->_error(self::DIRWRITE);
            return false;
        }

    }


    public function setFail()
    {
        $this->_error(self::DIRWRITE);
        return false;

    }

}
