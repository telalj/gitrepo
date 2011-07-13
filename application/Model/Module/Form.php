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
 * @package    Module
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Module_Form
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

        self::$form->addElementPrefixPath('Helper_Decorator','Helper/Decorator/','decorator');
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function mainConfiguration($data)
    {
        self::$form->setAction('admin/configure/index')
            ->setMethod('post')
            ->setAttrib('id', 'edit-configuration')
            ->setAttrib('name', 'edit-configuration');

        //emailType
        $emailType = self::$form->createElement('select', 'emailType')
            ->setLabel('Field_Configure_Email_Type')
            ->addMultiOption('sendmail', 'Sendmail')
            ->addMultiOption('smtp', 'SMTP')
            ->setValue($data->config->email->type);

        //emailFromEmail
        $emailFromEmail = self::$form->createElement('text', 'emailFromEmail')
            ->setLabel('Field_Configure_Email_From_Email')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Email_From_Email_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Email_From_Email_EmailAddress'))
            ->setAttrib('size', 40)
            ->setValue($data->config->email->from->email);

        //emailFromName
        $emailFromName = self::$form->createElement('text', 'emailFromName')
            ->setLabel('Field_Configure_Email_From_Name')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Email_From_Name_NotEmpty'))
            ->setAttrib('size', 40)
            ->setValue($data->config->email->from->name);

        //emailUser
        $emailUser = self::$form->createElement('text', 'emailUser')
            ->setLabel('Field_Configure_Email_User')
            ->setAttrib('size', 40)
            ->setValue($data->config->email->from->user);

        //emailPass
        $emailPass = self::$form->createElement('text', 'emailPass')
            ->setLabel('Field_Configure_Email_Pass')
            ->setAttrib('size', 40)
            ->setValue($data->config->email->from->pass);

        //emailHost
        $emailHost = self::$form->createElement('text', 'emailHost')
            ->setLabel('Field_Configure_Email_Host')
            ->setAttrib('size', 40)
            ->setValue($data->config->email->from->host);

        //emailAdmin
        $emailAdmin = self::$form->createElement('text', 'emailAdmin')
            ->setLabel('Field_Configure_Email_Admin')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Email_Admin_NotEmpty'))
            ->addValidator('EmailAddress', true, array('messages' => 'Error_Email_Admin_EmailAddress'))
            ->setAttrib('size', 40)
            ->setValue($data->config->email->admin);

        //dateformat
        $dateformat = self::$form->createElement('select', 'dateformat')
            ->setLabel('Field_Configure_Date_Format')
            ->addMultiOption('M d Y', 'M d Y  ' . date('M d Y', time()) )
            ->addMultiOption('m-d-y', 'm-d-y  ' . date('m-d-y', time()) )
            ->addMultiOption('Y M d', 'Y M d  ' . date('Y M d', time()) )
            ->addMultiOption('Y-m-d', 'Y-m-d ' . date('Y-m-d', time() ) )
            ->setValue($data->config->dateformat);

        //timeformat
        $timeformat = self::$form->createElement('select', 'timeformat')
            ->setLabel('Field_Configure_Time_Format')
            ->addMultiOption('M d Y h:i A', 'M d Y h:i A  ' . date('M d Y h:i A', time()) )
            ->addMultiOption('M-d-Y h:i A', 'm-d-y h:i A ' . date('m-d-y h:i A', time()) )
            ->addMultiOption('Y M d h:i A', 'Y M d h:i A ' . date('Y M d h:i A', time()) )
            ->addMultiOption('Y-m-d h:i A', 'Y-m-d h:i A' . date('Y-m-d h:i A', time() ) )
            ->setValue($data->config->timeformat);

        //lastFMKey
        $lastFMKey = self::$form->createElement('text', 'lastFMKey')
            ->setLabel('Field_Configure_lastFMKey')
            ->setAttrib('size', 40)
            ->setValue($data->media->lastFMKey);

        //downLoadImages
        $downLoadImages = self::$form->createElement('checkbox', 'downLoadImages')
            ->setLabel('Field_Configure_downLoadImages')
            ->setCheckedValue(1)
            ->setUncheckedValue(0) 
            ->setValue($data->media->downLoadImages);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		

        self::$form
            ->addElement($emailType)
            ->addElement($emailUser)
            ->addElement($emailPass)
            ->addElement($emailHost)
            ->addElement($emailFromEmail)
            ->addElement($emailFromName)
            ->addElement($emailAdmin)
            ->addElement($dateformat)
            ->addElement($timeformat)
            ->addElement($lastFMKey)
            ->addElement($downLoadImages)
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('emailType', 'emailUser', 'emailPass', 'emailHost', 'emailFromEmail', 'emailFromName', 'emailAdmin'), 'email', array('legend' => 'Form_Legend_Email', ));

        self::$form->addDisplayGroup(array('dateformat','timeformat'), 'date', array('legend' => 'Form_Legend_Date_Format', ));

        self::$form->addDisplayGroup(array('lastFMKey', 'downLoadImages', 'submit','no_csr'), 'lastfm', array('legend' => 'Form_Legend_Last_FM', ));


        
        return self::$form;            
    }

    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function accountForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-account')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-account')
            ->setAttrib('name', 'edit-module-account');

        
        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        //register
        $register = self::$form->createElement('select', 'register')
            ->setLabel('Field_Account_Register')
            ->setRequired(true)
            ->addMultiOption('open', 'Field_Register_Open')
            ->addMultiOption('invite', 'Field_Register_Invite')
            ->addMultiOption('verify', 'Field_Register_Verify')
            ->setValue($data->register);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($register)
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)        
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout', 'register'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function defaultForm($data)
    {// enabled
        $enabled = self::$form->createElement('select', 'enabled')
            ->setLabel('Field_Module_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);
        self::$form->setAction('admin/configure/edit-module-default')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-default')
            ->setAttrib('name', 'edit-module-default');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)      
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        
            
        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function searchForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-search')
            ->setMethod('post')
            ->setAttrib('id', 'edit-search-admin')
            ->setAttrib('name', 'edit-search-admin');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange) 
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)       
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function fileForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-file')
            ->setMethod('post')
            ->setAttrib('id', 'edit-file-admin')
            ->setAttrib('name', 'edit-file-admin');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)        
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        
  

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function playForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-play')
            ->setMethod('post')
            ->setAttrib('id', 'edit-play-admin')
            ->setAttrib('name', 'edit-play-admin');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)        
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));
        
        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));


        return self::$form;    
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function pictureForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-picture')
            ->setMethod('post')
            ->setAttrib('id', 'edit-picture-admin')
            ->setAttrib('name', 'edit-picture-admin');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)       
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));


        return self::$form; 
    }

    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function adminForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-admin')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-admin')
            ->setAttrib('name', 'edit-module-admin');
      
        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)        
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('layout'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object 
     */
    public function artistForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-artist')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-artist')
            ->setAttrib('name', 'edit-module-artist');


        // enabled
        $enabled = self::$form->createElement('select', 'enabled')
            ->setLabel('Field_Module_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // api
        $api = self::$form->createElement('select', 'api')
            ->setLabel('Field_Module_API')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // rss
        $rss = self::$form->createElement('select', 'rss')
            ->setLabel('Field_Module_RSS')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);


        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($enabled)
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange) 
            ->addElement($api)
            ->addElement($rss)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)       
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('enabled', 'layout','api', 'rss'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));
    
        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }

    
    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function albumForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-album')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-album')
            ->setAttrib('name', 'edit-module-album');


       // enabled
        $enabled = self::$form->createElement('select', 'enabled')
            ->setLabel('Field_Module_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // api
        $api = self::$form->createElement('select', 'api')
            ->setLabel('Field_Module_API')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // rss
        $rss = self::$form->createElement('select', 'rss')
            ->setLabel('Field_Module_RSS')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($enabled)
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange) 
            ->addElement($api)
            ->addElement($rss) 
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)      
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('enabled', 'layout', 'api','rss'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function contentForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-Content')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-Content')
            ->setAttrib('name', 'edit-module-Content');


        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);


        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)        
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array( 'layout',), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        
  
        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }


    /**
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function genreForm($data)
    {
        self::$form->setAction('admin/configure/edit-module-genre')
            ->setMethod('post')
            ->setAttrib('id', 'edit-module-genre')
            ->setAttrib('name', 'edit-module-genre');


        // enabled
        $enabled = self::$form->createElement('select', 'enabled')
            ->setLabel('Field_Module_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // layout
        $layout = self::$form->createElement('select', 'layout')
            ->setLabel('Field_Layout')
            ->setRequired(true)
            ->addMultiOption('1_column', 'Field_Layout_1_Column')
            ->addMultiOption('2_column_l', 'Field_Layout_2_Column_L')
            ->addMultiOption('2_column_r', 'Field_Layout_2_Column_R')
            ->addMultiOption('3_column', 'Field_Layout_3_Column')
            ->setValue($data->layout);

        //cacheEnable
        $cacheEnabled = self::$form->createElement('select', 'cache_enabled')
            ->setLabel('Field_Cache_Enabled')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->enable);

        // cacheLifetime
        $cacheLifetime = self::$form->createElement('text', 'cache_lifetime')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Cache_Lifetime_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Cache_Lifetime_Int'))
            ->setLabel('Field_Cache_Lifetime')
            ->setAttrib('size', 8)
            ->setValue($data->cache->lifetime);

        // cacheSerialization
        $cacheSerialization = self::$form->createElement('select', 'cache_serialization')
            ->setLabel('Field_Cache_Serialization')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->cache->serialization);

        // perPage
        $perPage = self::$form->createElement('text', 'per_page')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Per_Page_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Per_Page_Int'))
            ->setLabel('Field_Per_Page')
            ->setAttrib('size', 4)
            ->setValue($data->perPage);

        // pageRange
        $pageRange = self::$form->createElement('text', 'page_range')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Page_Range_NotEmpty'))
            ->addValidator('Int', true, array('messages' => 'Error_Page_Range_Int'))
            ->setLabel('Field_Page_Range')
            ->setAttrib('size', 4)
            ->setValue($data->pageRange);

        // api
        $api = self::$form->createElement('select', 'api')
            ->setLabel('Field_Module_API')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // rss
        $rss = self::$form->createElement('select', 'rss')
            ->setLabel('Field_Module_RSS')
            ->setRequired(true)
            ->addMultiOption(1, 'Field_Enabled_Yes')
            ->addMultiOption(0, 'Field_Enabled_No')
            ->setValue($data->enabled);

        // Guest view:create:update:delete:admin
        $guestAcl = self::$form->createElement('select', 'guest_Acl')
            ->setLabel('Field_Guest_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update:', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Guest);

        // Member
        $memberAcl = self::$form->createElement('select', 'member_Acl')
            ->setLabel('Field_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->Member);

        // PowerMember
        $powerMemberAcl = self::$form->createElement('select', 'power_member_Acl')
            ->setLabel('Field_Power_Member_Acl')
            ->addMultiOption('', 'Field_No_Permisions')
            ->addMultiOption('view', 'Field_View')
            ->addMultiOption('view:create', 'Field_View_Create')
            ->addMultiOption('view:create:update', 'Field_View_Create_Update')
            ->addMultiOption('view:create:update:delete', 'Field_View_Create_Update_Delete')
            ->addMultiOption('view:create:update:delete:admin', 'Field_View_Create_Update_Delete_Admin')
            ->setValue($data->acl->PowerMember);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Configure_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));
		
        self::$form
            ->addElement($enabled)
            ->addElement($layout)            
            ->addElement($cacheEnabled)
            ->addElement($cacheLifetime)
            ->addElement($cacheSerialization)
            ->addElement($perPage)
            ->addElement($pageRange)
            ->addElement($api)
            ->addElement($rss) 
            ->addElement($guestAcl)
            ->addElement($memberAcl) 
            ->addElement($powerMemberAcl)       
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array('enabled', 'layout', 'rss', 'api'), 'module_settings', array('legend' => 'Form_Legend_Module', ));

        self::$form->addDisplayGroup(array('cache_enabled', 'cache_lifetime', 'cache_serialization'), 'cache', array('legend' => 'Form_Legend_Cache', ));

        self::$form->addDisplayGroup(array('guest_Acl', 'member_Acl', 'power_member_Acl'), 'module_permissions', array('legend' => 'Form_Legend_ACL', ));        

        self::$form->addDisplayGroup(array('per_page', 'page_range', 'submit', 'no_csr'), 'module', array('legend' => 'Form_Legend_Pagination', ));

        return self::$form;
    }

    
    public function importFilterForm($parentId, $alpha, $page)
    {
        self::$form->setAction('admin/import/index/page/'.$page.'/parentId/'.$parentId.'/alpha/'.$alpha)
            ->setMethod('get')
            ->setAttrib('id', 'import-process')
            ->setAttrib('name', 'import-process');

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Import_Filter_Submit');

        self::$form
            ->addElement($submit);

        return self::$form;

    }


    /**
     * @access Public
     * @return Object
     */
    public function importForm($pathArray,$parentId, $alpha, $page)    
    {
        $fileDb = new Model_File_Db;

        //Zend_Dojo::enableForm(self::$form);

        self::$form->setAction('admin/import/index/page/'.$page.'/parentId/'.$parentId.'/alpha/'.$alpha)
            ->setMethod('post')
            ->setAttrib('id', 'import-process')
            ->setAttrib('name', 'import-process');

        
        foreach($pathArray as $path) {

            $label = '<div class="grid_3">' . $path['media_dir_name'] . '</div>';
            
            // child check
            if($fileDb->mediaDirHasChild($path['media_dir_id'])) {
                $label .= '<div class="grid_9"><a href="admin/import/index/parentId/'.$path['media_dir_id'].'" title="">'  . $path['media_dir_path'] .'</a></div>';
            } else {
                $label .= '<div class="grid_9">' . $path['media_dir_path'] .'</div>';
            }

            // date check
            if($path['media_dir_last_scan'] > 0) {
                $label .= '<div class="grid_2">' . date(Zend_Registry::get('configuration')->config->dateformat,$path['media_dir_last_scan']) . '</div>';
            } else {
                $label .= '<div class="grid_2">'.self::$translate->translate('Field_Import_Never_Scanned').'</div>';
            }
            $label .= '<div class="clear"></div>';

             $element = self::$form->createElement('Checkbox', $path['media_dir_id'])
                ->setLabel($label)
                ->setCheckedValue($path['media_dir_path'])
                ->setUncheckedValue(0)
                ->setDecorators(array('CheckBox'));
            self::$form->addElement($element);
        }
    
        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Import_Submit');
                 
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

        self::$form
            ->addElement($csr)
            ->addElement($submit);

       

        return self::$form;
    }

    
    public function importGenreForm()
    {
        self::$form->setAction('admin/import/genre')
            ->setMethod('post')
            ->setAttrib('id', 'import-genre')
            ->setAttrib('name', 'import-genre');

    
         // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Import_Genre_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($csr)
            ->addElement($submit);

         self::$form->addDisplayGroup(array( 'submit', 'no_csr'), 'import-genre', array('legend' => 'Form_Legend_Import_Genre', ));

        return self::$form;
    }


    /**
     * @access Public
     * @return Object
     */
    public function importDirectoryForm()    
    {
        self::$form->setAction('admin/import/directory')
            ->setMethod('post')
            ->setAttrib('id', 'import-directory')
            ->setAttrib('name', 'import-directory');

         // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Import_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($csr)
            ->addElement($submit);

         self::$form->addDisplayGroup(array( 'submit', 'no_csr'), 'import-genre', array('legend' => 'Form_Legend_Import_Directory', ));

        return self::$form;
    }


    /**
     * @access Public
     * @return Object
     */
    public function importUploadForm()    
    {
        $config   = Zend_Registry::get('configuration');

        $rootPath = Zend_Registry::get('siteRootDir');

        $path = $rootPath . $config->media->path;

        self::$form->setAction('admin/import/upload')
            ->setMethod('post')
            ->setAttrib('id', 'import-upload')
            ->setAttrib('name', 'import-upload');


        return self::$form;
    }
}
