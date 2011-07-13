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
 * @subpackage Breadcrumb
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Breadcrumb.php 4 2009-6-1 Jaimie $
 */
class Helper_Breadcrumb
{

    /** 
     * @access Public
     * @param Array $crumbs
     * @return String
     */
    public static function process($crumbs = NULL)
    {
        if(is_array($crumbs)) {
            $count = count($crumbs);
            for($i = 0; $i < $count; $i++) {
                if($i != ($count - 1)) {
                    $output[] = '<a title="'. $crumbs[$i]['title'] .'" href="' . $crumbs[$i]['url'] .'">'. $crumbs[$i]['title'] .'</a>';
                } else {
                    $output[] = '<a class="current" title="'. $crumbs[$i]['title'] .'">' . $crumbs[$i]['title'] .'</a>';
                }
            }

        } else {

            $output = NULL;
        }
        return $output;
    }
}
