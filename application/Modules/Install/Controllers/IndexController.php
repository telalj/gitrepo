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
 * @package    Install
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: IndexController.php 4 2009-6-1 Jaimie $
 */
class Install_IndexController extends Zend_Controller_Action
{

    /* @access Public
     * @var object
     */
    private static $installDb   = null;

    /* @access Public
     * @var object
     */
    private static $installForm = null;

    /* @access Public
     * @var object
     */
    private static $installMail = null;


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {       
        self::$installForm = new Model_Install_Form;

        $config = Zend_Registry::get('configuration');

        if($config->installed) {
            $this->_redirect('index');
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {        
        $error = false;
        $this->view->databaseConfig = true;
        $this->view->config         = true;
        $this->view->media          = true;
        $this->view->cache          = true;

        // pre validation Database.ini
        if(!is_writable(Zend_Registry::get('siteRootDir').'/application/Configs/Database.ini')) {
            $error = true;
            $this->view->databaseConfig = false;
        }

        // pre validation Config.ini
        if(!is_writable(Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini')) {
            $error = true;
            $this->view->config = false;
        }

        // pre validation media directory
        if(!is_writable(Zend_Registry::get('siteRootDir').'/data/media')) {
            $error = true;
            $this->view->media = false;
        }
        
        // prevalidation cache
        if(!is_writable(Zend_Registry::get('siteRootDir').'/data/cache/')) {
            $error = true;
            $this->view->cache = false;
        }

        $form = self::$installForm->dbForm();
       

        // if we have post
        if ($this->getRequest()->isPost()) {

            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                // write config
                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Database.ini',
                              null,
                              array('skipExtends'        => true,
                                    'allowModifications' => true));

                $config->default->params->host     = $values['host'];
                $config->default->params->dbname   = $values['dbname'];
                $config->default->params->username = $values['username'];
                $config->default->params->password = $values['password'];

                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Database.ini'));
                $writer->write();
                
                // create database use native mysql to do this
                @$link = mysql_connect($values['host'], $values['username'], $values['password']);

                if (!$link) {
                    $this->view->createDbError = true;
                    $this->view->dbError       = $this->view->translate('Error_Database_Connect_Fail');
                    $this->view->form          = $form;
                 
                } else {
                    $sql = "CREATE DATABASE `" . $values['dbname'] . "`";
                    if (!mysql_query($sql, $link)) {   
                        $this->view->createDbError = true;
                        $this->view->dbError       = mysql_error($link); 
                        $this->view->form = $form;                                                              
                    } else {
                        $db_selected = mysql_select_db($values['dbname'], $link);
                        if (!$db_selected) {
                            $this->view->createDbError = true;
                            $this->view->dbError       = mysql_error($link);
                            $this->view->form = $form; 
                        } else {

                            // import tables
                            $filename = Zend_Registry::get('siteRootDir').'/data/voodoo.sql';
                            $sql = file_get_contents($filename, true);
                           
                            $queries = explode(';', $sql);

                            foreach ($queries as $query){                           
                               if (strlen(trim($query)) > 0) {
                                    if( !mysql_query($query)) {
                                        $this->view->createDbError = true;
                                        $this->view->dbError       = mysql_error($link);
                                        // drop the Db there was an error
                                        //mysql_query("DROP DATABASE `" . $values['dbname'] . "`");
                                        break;
                                    }
                                }
                            } 

                            if (!$this->view->dbError) {
                                // set install to 1
                                $config = new Zend_Config_Ini(Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini',
                                  null,
                                  array('skipExtends'        => true,
                                    'allowModifications' => true));

                                $config->default->installed = 1;
                                
                                $writer = new Zend_Config_Writer_Ini(array('config'   => $config,
                                           'filename' => Zend_Registry::get('siteRootDir').'/application/Configs/Config.ini'));
                                $writer->write();

                                // redirect to install step 2
                                $this->_redirect('install/create-admin');
                            } else {
                                $this->view->form = $form;                                 
                            }
                        }
                    }
                }

            }
        } else {
             $this->view->error = $error;

            if ( !$error){
                $this->view->form = $form;
            } else {
                $this->form = '';              
            }
        }

       
    }
}
