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
 * @subpackage Element
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Language.php 4 2009-6-1 Jaimie $
 */
class Element_Language extends Zend_Form_Element_Select
{
    
    /* @access Private
     * @var object
     */
    public static $languageDb = null;

    /** 
     * @access Public
     * @return void
     */
    public function init()
    {
        // get langarray
        self:: $languageDb = new Model_Language_Db;       

        $languages =  self:: $languageDb->getActiveLanguages();       

        $this->setLabel('Field_Language')
            ->setvalue($data['language']);

        foreach ($languages as $lang ) {
            $this->addMultiOption($lang['languages_id'],$lang['name']);
        }
               
    }

}
