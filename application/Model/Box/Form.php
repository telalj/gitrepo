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
 * @package    Box
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Box_Form
{
    /* @access Public
     * @var object
     */
    private static $form;

    /** Contructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$form = new Zend_Form();
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function editForm($data)
    {

        // Submit
        $submit = self::$form->createElement('submit', 'submit' );
        $submit->setAttrib('class','formSubmit');
        $submit->setLabel('Field_Account_Login_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
       
        self::$form->addElement($submit)
            ->addElement($csr);

        return self::$form;
    }

}
