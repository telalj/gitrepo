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
 * @version    $Id: ContentExists.php 4 2009-6-1 Jaimie $
 */
class Zend_Validate_ContentExist extends Zend_Validate_Abstract
{
    /**
     * @var String
     */
    const EXISTS = 'exists';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::EXISTS => "The content name '%value%' has already been used.",        
    );

   
    /**
     * @access Public
     * @param String $value
     * @param String $context
     * @return Bool
     */
    public function isValid($value, $context = null)
    {
        $contentObj = new Model_Content_Db;

        $find = array("/[^a-zA-Z0-9\s]/","/\s+/");
        $replace = array(" ","-");

        $contentPage = strtolower(preg_replace($find,$replace,$value));

        $content = $contentObj->getContentIdByPageName($contentPage);
        

        if ( $content > 0 ) {  
            $this->_error(self::EXISTS);
            return false;
        } else {
            return true;
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
