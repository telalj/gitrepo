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
 * @package    Admin
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: AccountType.php 4 2009-6-1 Jaimie $
 */
class  Zend_View_Helper_AccountType
{

    /**
     * @access Public
     * @param String $accountType
     * @return String 
     */
    public function accountType($accountType)
    {
        
        $translate = Zend_Registry::get('Zend_Translate');

        switch ($accountType){
            case 'Guest':
                $outPut = $translate->translate('Field_Account_Type_Guest');
            break;

            case 'Member':
                $outPut = $translate->translate('Field_Account_Type_Member');
            break;

            case 'Administrator':
                $outPut = $translate->translate('Field_Account_Type_Administrator');
            break;

            default:
                $outPut = $translate->translate('Field_Account_Type_Guest');
            break;
        }

        return $outPut;

    }
}
