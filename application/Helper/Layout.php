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
 * @subpackage Layout
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: layout.php 4 2009-6-1 Jaimie $
 */
class Helper_Layout extends Zend_Layout_Controller_Plugin_Layout
{

    /** 
     * @access Public
     * @param Object Zend_Controller_Request_Abstract
     * @param Object $request
     * @return Void
     */
    public function preDispatch (Zend_Controller_Request_Abstract $request)
    {

        // get configs
        $siteRootDir = Zend_Registry::get('siteRootDir');
        $config      = Zend_Registry::get('moduleConfig');

        // Get view Object
        $view = $this->getLayout()->getView();

        // Set Doc Type
        $doctypeHelper = new Zend_View_Helper_Doctype();
        $doctypeHelper->doctype('XHTML1_STRICT');

        // Set up Meta Tags
        $title       = 'Meta_'.ucfirst($request->getModuleName()).'_'.ucfirst($request->getControllerName()).'_Title';
        $description = 'Meta_'.ucfirst($request->getModuleName()).'_'.ucfirst($request->getControllerName()).'_Description';        
        $keywords    = 'Meta_'.ucfirst($request->getModuleName()).'_'.ucfirst($request->getControllerName()).'_Keywords';

        // Assign Meta Tags if we are not using the default Module since that is created by content Table
        $view->headTitle($view->translate($title));
       

        // Set Page Title
        $view->pageTitle = $view->translate($title);
        
        // set meta
        $view->headMeta()            
            ->appendHttpEquiv('description', $view->translate($description))
            ->appendHttpEquiv('keywords', $view->translate($keywords));

        // Set Up Base URL     
        $view->baseUrl = "http://".$_SERVER['SERVER_NAME'].$request->getBaseUrl()."/";

        // get theme
        $themeConfig = new Zend_Config_Ini( $siteRootDir . '/application/Configs/Theme.ini', 'default' );

        Zend_Registry::set('themeDir', $themeConfig->directory);

        // set theme path
        if ( $request->getModuleName() != 'admin') {
            $theme = $themeConfig->directory;
            $view->setBasePath($siteRootDir . '/themes/'.$theme.'/'.$request->getModuleName());
            Zend_Registry::set('theme', $themeConfig);
        
            // setup the layout
            $layout = Zend_Registry::get('Zend_Layout');
            $layout->setLayoutPath($siteRootDir . '/themes/'.$theme.'/layouts');
         }   
         $view->addHelperPath('Zend/Dojo/View/Helper/', 'Zend_Dojo_View_Helper');
        
       
        // breadcrumb
        $breadcrumbs[] = array('title' => $view->translate('Menu_Home'), 'url' => $view->baseUrl);
        if ($request->getModuleName() != 'default') {
            $breadcrumbs[] = array('title' => $view->translate('Menu_'.ucfirst($request->getModuleName())), 'url' => $request->getModuleName().'/');
        }
    
        if ( $request->getControllerName() != 'error') {
            $breadcrumbs[] = array('title' => $view->translate('Menu_'.ucfirst($request->getModuleName()).'_'.ucfirst($request->getControllerName()) ), 'url' => $request->getModuleName().'/'.$request->getControllerName() );
        }

       

        if ($request->getActionName() != 'index') {
         $breadcrumbs[] = array('title' => $view->translate('Menu_'.ucfirst($request->getModuleName()).'_'.ucfirst($request->getControllerName()).'_'.ucfirst($request->getActionName()) ), 'url' => $request->getModuleName().'/'.$request->getControllerName().'/'.$request->getActionName() );
        }

        $view->placeholder('breadcrumbs')->exchangeArray( Helper_Breadcrumb::process($breadcrumbs) );

        // Switch Layout
        $moduleName = $request->getModuleName();

        $path = $this->getLayout()->getLayoutPath() . DIRECTORY_SEPARATOR . $config->module->$moduleName->layout;

        $this->getLayout()->setLayoutPath($path);

        Zend_Registry::set('layout', $config->module->$moduleName->layout);

        // disable output buffering
        if( $request->getControllerName() == 'import' && $request->getActionName() == 'process') {
            $front = Zend_Controller_Front::getInstance();
            $front->getDispatcher()->setParam('disableOutputBuffering', true);            
        }


    }



}

