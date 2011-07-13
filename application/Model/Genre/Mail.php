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
 * @package    Genre
 * @subpackage Mail
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Mail.php 4 2009-6-1 Jaimie $
 */
class Model_Genre_Mail
{
    /* @access Public
     * @var object
     */
    private static $mail      = null;

    /* @access Public
     * @var object
     */
    private static $config    = null;

    /* @access Public
     * @var object
     */
    private static $translate = null;


    /**
     * Class constructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$mail      = new  Zend_Mail(); 

        self::$config    = Zend_Registry::get('configuration');

        self::$translate = Zend_Registry::get('Zend_Translate');

        // set transport
        switch(self::$config->config->email->type)
        {
            case 'sendmail':
                self::$mail->setDefaultTransport(new Zend_Mail_Transport_Sendmail());
            break;

            case 'smtp':
                $tr = new Zend_Mail_Transport_Smtp(self::$config->config->email->host);
                self::$mail->setDefaultTransport($tr);
            break;
        }
       
        // set from
        self::$mail->setFrom(self::$config->config->email->from->email,self::$config->config->email->from->name);
    }

}
