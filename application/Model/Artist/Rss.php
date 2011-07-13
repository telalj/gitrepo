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
 * @subpackage Rss
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Rss.php 4 2009-6-1 Jaimie $
 */
class Model_Artist_Rss
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

    /**
     * Class constructor
     * @access Public
     * @return Void
     */
    public function __construct()
    {
        self::$db = Zend_Registry::get('Zend_Db');

        $moduleConfig = Zend_Registry::get('moduleConfig');
        self::$config = $moduleConfig->module->artist;

        $cacheDir = Zend_Registry::get('siteRootDir') .'/data/cache/' .self::$config->cache->dir;

        $frontendOptions = array('lifetime' => self::$config->cache->lifetime, 'automatic_serialization' => self::$config->cache->serialization);
        $backendOptions  = array('cache_dir' => $cacheDir);

        self::$cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
    }


    /**
     * @access Public
     * @param Int $page
     * @return Object
     */
    public function getAllArtists($page)
    {

        $sql = self::$db->select()->from("artist");
        
        $paginator = Zend_Paginator::factory($sql);

        $paginator->setCurrentPageNumber($page);

        $paginator->setItemCountPerPage(self::$config->perPage);

        $paginator->setPageRange(self::$config->pageRange);

        return $paginator;
    }

}
