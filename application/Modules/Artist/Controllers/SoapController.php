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
 * @package    Artist
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: SoapController.php 4 2009-6-1 Jaimie $
 */
class Artist_SoapController extends Zend_Controller_Action
{

    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        // load registry
        $registry = Zend_Registry::getInstance();
        
        // if module is active
        if ( !$registry->get('moduleConfig')->module->artist->enabled ) {
            $this->_redirect('/error/module-not-enabled/module/Artist');
        }

        if(!$registry->get('moduleConfig')->module->artist->api) {
            $this->_redirect('/error/feature-not-enabled');
        }

        Zend_Loader::loadClass('Zend_Soap_Server');
        Zend_Loader::loadClass('Zend_Soap_AutoDiscover');

        $this->_helper->layout()->disableLayout();

        $this->_helper->viewRenderer->setNoRender(true);
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {        

        if(isset($_GET['wsdl']))
        {
            $wsdl = new Zend_Soap_AutoDiscover();
            $wsdl->setClass('Model_Artist_Soap');
            $wsdl->handle();
        } else {
            $server = new Zend_Soap_Server(Zend_Registry::get('siteRootUrl').'/artist/soap?wsdl');
            $server->setClass('Model_Artist_Soap');
            $server->handle();
        }

        
    }
}
