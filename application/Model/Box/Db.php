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
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Box_Db
{
    /* @access Public
     * @var object
     */
    private static $db;

    /* @access Public
     * @var object
     */
    private static $cache;

    /* @access Public
     * @var object
     */
    private static $config;

   
    /**
     * Class constructor
     * @access Public
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        self::$config   = new Zend_Config_Ini(Zend_Registry::get('siteRootDir') . '/application/Configs/Box.ini', 'default');
        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache' .self::$config->cache->dir;


        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }

    
    /**
     * @access Public
     * @param String $layout
     * @param String $side
     * @return Array
     */
    public function getBoxLayout($layout, $side)
    {
        $sql = self::$db->select()
            ->from( array('b' => 'box'), array('box_id', 'module', 'column_side') )
            ->where('b.layout = ?', $layout)
            ->where('b.column_side = ?', $side)
            ->where('b.box_enabled = ?', '1')
            ->order(array('b.box_order DESC'));

        $result = self::$db->query($sql)->fetchAll();

        return $result;   
    }


}
