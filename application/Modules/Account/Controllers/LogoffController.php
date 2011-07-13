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
 * @version    $Id: LogoffController.php 4 2009-6-1 Jaimie $
 */
class Account_LogoffController extends Zend_Controller_Action
{

 
    /** 
     * @access Public
     * @return Void
     */
    public function init()
    {
        // load registry
        $registry         = Zend_Registry::getInstance();

        if ( !$registry->get('moduleConfig')->module->account->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Account');
        }
    }


    /** 
     * @access Public
     * @return Void
     */
	public function indexAction()
    {
        require_once 'Zend/Auth.php';

    	$auth = Zend_Auth::getInstance();
		$auth->clearIdentity();

        Zend_Session::namespaceUnset('Default');
		$this->_redirect('/');      
	}

	
} 
