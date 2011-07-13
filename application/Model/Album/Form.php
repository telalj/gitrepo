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
 * @package    Album
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */ 
class Model_Album_Form
{
    /* @access Public
     * @var object
     */
    private static $form         = null;

    /* @access Public
     * @var object
     */
    private static $translate    = null;

    /** Contructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$form = new Zend_Form();

        self::$translate = Zend_Registry::get('Zend_Translate');
    
        self::$form->setTranslator(self::$translate);

        self::$form->addPrefixPath('Element', 'Helper/Element/', 'element');
    }


    /**
     * @access Public
     * @param Array $data
     * @return Void
     */
    public function editAlbumForm($data)
    {


    }
}
