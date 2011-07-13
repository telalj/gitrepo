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
 * @package    Language
 * @subpackage Db
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Db.php 4 2009-6-1 Jaimie $
 */
class Model_Language_Db
{
    /* @access Public
     * @var object
     */
    private static $db      = null;

    /* @access Public
     * @var object
     */
    private static $cache   = null;

    /* @access Public
     * @var object
     */
    private static $config  = null;

    /* @access Public
     * @var object
     */
    private static $debug   = false;

    
    /**
     * Class constructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        self::$config   = new Zend_Config_Ini(Zend_Registry::get('siteRootDir') . '/application/Configs/Language.ini', 'default');
        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }


    /**
     * @access Public
     * @return Array
     */
    public function getActiveLanguages()
    {

        if (!($results = self::$cache->load('getActiveLanguages'))) {

            $sql = self::$db->select()
                ->from('languages')
                ->where('languages_active = 1');

            $results = self::$db->query($sql)->fetchAll();

            self::$cache->save($results);
        }

        return $results;
    }


    /**
     * @access Public
     * @param Int $langaugeId
     * @return Array
     */
    public function getLanguageById($langaugeId)
    {
        if (!($results = self::$cache->load('getLanguageById_'.$langaugeId))) {

            $sql = self::$db->select()
                ->from('languages')
                ->where('languages_id = ?', $langaugeId);

            $results = self::$db->query($sql)->fetch();

            self::$cache->save($results);
        }

        return $results;
    }

}
