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
 * @package    Admin
 * @subpackage Controller
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: CountryController.php 4 2009-6-1 Jaimie $
 */
class Admin_CountryController extends Zend_Controller_Action
{
    /* @access Public
     * @var object
     */
    private static $accountType = null;

    /* @access Public
     * @var object
     */
    private static $auth        = null;

    /* @access Public
     * @var object
     */
    private static $acl         = null;

    /* @access Public
     * @var object
     */
    private static $countryDb   = null;

    /* @access Public
     * @var object
     */
    private static $countryForm = null;
    
    /* @access Public
     * @var object
     */
    private static $config     = null; 


    /** 
     * @access Public
     * @return void
     */
    public function init()
    {      
        // load registry
        $registry = Zend_Registry::getInstance();

        self::$config = $registry->get('moduleConfig')->module->admin;
        self::$acl    = $this->view->acl;        
        self::$auth   = $this->view->auth;
        
        /** Acls */
        if(self::$auth->hasIdentity()) {
            $accountId   = self::$auth->getIdentity()->account_id;
            $accountType = self::$auth->getIdentity()->account_type;
        } else {
            $accountId   = 0;
            $accountType = 'Guest';
        }
        
        foreach (self::$config->acl as $key => $val) {
            $resourceArray = explode(':', $val);
            self::$acl->allow($key,  null,   $resourceArray);            
        }    

        if(!self::$acl->isAllowed($accountType, null, 'admin') ? "1" : "0") {
            $this->_redirect('error/access-denied/from/admin:country');
        }
       
        self::$countryDb   = new Model_Country_Db;

        self::$countryForm = new Model_Country_Form;
    }
    
    
    /** 
     * @access Public
     * @return void
     */
    public function indexAction()
    {
        
        // page number from request
        $page = $this->getRequest()->getParam('page');

        $paginator = self::$countryDb->getActiveCountries($page);
        $this->view->paginator = $paginator;  
    }

    
    /** 
     * @access Public
     * @return void
     */
    public function viewAction()
    {
        // page number from request
        $page = $this->getRequest()->getParam('page');

        $countryId = (int)$this->getRequest()->getParam('country_id');

        $paginator = self::$countryDb->getZonesByCountry($page,$countryId);
        $this->view->paginator = $paginator; 

    }


    /** 
     * @access Public
     * @return void
     */
    public function addAction()
    {
        $form = self::$countryForm->addForm();


         // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                $data = array(
                    'geo_zone_name'        => $values['geo_zone_name'],
                    'geo_zone_description' => $values['geo_zone_description'],
                    'date_added'           => time(),
                    'countries_id'         => $values['countries_id']
                );
              
                self::$countryDb->addCountry($data);

                $this->_redirect('admin/country/index');
            } 
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function removeAction()
    {

        $countryId = (int)$this->getRequest()->getParam('country_id');

        if ( $countryId < 1) {
            throw new exception('Error missing required parameter: country_id');
        }

        // get the country
        $country = self::$countryDb->getCountry($countryId);
        $this->view->country = $country;
        

        $form = self::$countryForm->removeForm();

        // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                
                self::$countryDb->remove($countryId);

                $this->render('remove-complete');
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function updateAction()
    {

        $form = self::$countryForm->updateForm();

        // if we have post
        if ($this->getRequest()->isPost()) {
            // If form is not valid 
            if (!$form->isValid($_POST)) {
                $this->view->form = $form;
            } else {
                $values = $form->getValues();

                // clear out tables
                self::$countryDb->truncateZones();

                // load new countries
                self::$countryDb->loadCountries($values['country_file']);

                // load new regions
                self::$countryDb->loadRegions($values['region_file']);

                // load new cities 
                self::$countryDb->loadCities($values['cities_file']); 
    
                $this->render('update-complete');               
            
            }
        } else {
            $this->view->form = $form;
        }
    }


    /** 
     * @access Public
     * @return void
     */
    public function removeZoneAction()
    {


    }

}
