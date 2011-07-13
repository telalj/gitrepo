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
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: SubmenuController.php 4 2009-6-1 Jaimie $
 */
class SubmenuController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $themeConfig = null;

    /* @access Public
     * @var object
     */
    private static $siteRootDir = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {
        self::$themeConfig =  Zend_Registry::get('theme');

        self::$siteRootDir = Zend_Registry::get('siteRootDir');

        $theme = self::$themeConfig->directory;

        $this->view->addScriptPath(self::$siteRootDir . '/themes/'.$theme.'/default/scripts');      
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {

          
    }
 

    /** 
     * @access Public
     * @return void
     */
    public function accountAction()
    {
        
    }

}
