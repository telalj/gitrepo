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
 * @subpackage Translate
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Translate.php 4 2009-6-1 Jaimie $
 */
class Helper_Translate  extends Zend_Controller_Plugin_Abstract 
{

     /** 
     * @access Public
     * @param Object Zend_Controller_Request_Abstract
     * @param Object $request
     * @return Void
     */
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        
        Zend_Locale::$compatibilityMode = false;

        // set default local need to change this to except a param from the request or get from a cookie
        //$requestLanguage = (int)$_COOKIE['language'];
        

        if ( !empty($requestLanguage) ) {
            $langDb     = new Model_Language_Db;
            $lang       = $langDb->getLanguageById($requestLanguage);
            $language   = $lang['code'];
            $languageId = $lang['languages_id'];         
        } else {
            $language   = 'en_US';
            $languageId = 1;
        }
        

        try {
            $locale = new Zend_Locale($language);
        } catch (Zend_Locale_Exception $e) {
            $locale = new Zend_Locale('en_US');
        }

        Zend_Registry::set('Zend_Locale', $locale);

    
        // set translator up
        $options = array('scan' => Zend_Translate::LOCALE_FILENAME);

        $translate = new Zend_Translate('tmx',  Zend_Registry::get('siteRootDir')  . '/application/languages/default/' , 'auto', $options);
        if ($module != 'default'){
            $translate->addTranslation( Zend_Registry::get('siteRootDir').'/application/languages/'.$module.'/' );
        }

        $translate->setLocale($locale);

        Zend_Form::setDefaultTranslator($translate);

        setcookie('language', $languageId, null, '/');

        Zend_Registry::set('language' , $languageId);

        Zend_Registry::set('Zend_Translate', $translate);  
    }
}
