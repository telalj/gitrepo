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
 * @package    Content
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Content_Form
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
     * @return  Object
     */
    public function addForm()
    {

        $contentObj  = new Model_Content_Db;
        $parentArray = $contentObj->getParentPageNames();

        $config      = Zend_registry::get('configuration');
        $siteRootDir = Zend_registry::get('siteRootDir');

        $contentDir = $siteRootDir . $config->content->pagePath;
        
        Zend_Dojo::enableForm($form);

        // content_page
        $contentPage = self::$form->createElement('text', 'content_page')
            ->addPrefixPath('validators', 'validators', Zend_Form_Element::VALIDATE)
            ->setRequired(true)
            ->setLabel('Field_Content_Page')
            ->addValidator('Writable', true, array($contentDir, 'messages' => 'The page storage ' . $contentDir . ' is not writable!' ))
            ->addValidator('ContentExist', true, array( 'messages' => 'The page name ' . $contentPage->getvalue() . ' has already been used!' ))
            ->addValidator('NotEmpty', true, array('messages' => 'Page Name is required!' ))
            ->addValidator('Alnum', true, array('allowWhiteSpace' => true, 'messages' => '2 to 40 alpha-numeric characters only for the Page Name!' ))
            ->addValidator('stringLength', true, array(2, 40,'messages' => '4 to 40 alpha-numeric characters only for the Page Name!'))
            ->setDescription('Field_Content_Page_Description');

        // content_in_menu
        $contentInMenu = $form->createElement('Checkbox', 'content_in_menu'); 
        $contentInMenu->setLabel($translate->translate('Field_Content_In_Menu'));
        $contentInMenu->setDescription($translate->translate('Field_Content_In_Menu_Description'));

        //content_protected
        $contentProtected = $form->createElement('Checkbox', 'content_protected'); 
        $contentProtected->setLabel($translate->translate('Field_Content_Protected'));
        $contentProtected->setDescription($translate->translate('Field_Content_Protected_Description'));

        // content_layout
        $contentLayout = $form->createElement('select', 'content_layout');
        $contentLayout->setLabel($translate->translate('Field_Content_Layout'));
        $contentLayout->setRequired(true);
        $contentLayout->addValidator('NotEmpty', true, array('messages' => 'Page Layout is required!' ));
        $contentLayout->setDescription($translate->translate('Field_Content_Layout_Description'));
        $contentLayout->addMultiOption('1_column', 'Single Column');
        $contentLayout->addMultiOption('2_column_l', '2 Column Left Menu');
        $contentLayout->addMultiOption('2_column_r', '2 Column Right Menu');
        $contentLayout->addMultiOption('3_column', '3 Column');

        // content_parent_page
        $contentParentPage = $form->createElement('select', 'content_parent_page');
        $contentParentPage->setLabel($translate->translate('Field_Content_Parent_Page'));
        $contentParentPage->setDescription($translate->translate('Field_Content_Parent_Page_Description'));
        $contentParentPage->addMultiOption('0', 'Main Page ( No Parent)');
        foreach ( $parentArray as $parent ) {
            $contentParentPage->addMultiOption($parent['content_id'], $parent['content_page']);
        }

        // content_status
        $contentStatus = $form->createElement('select', 'content_status');
        $contentStatus->setLabel($translate->translate('Field_Content_Status'));
        $contentStatus->setRequired(true);
        $contentStatus->addValidator('NotEmpty', true, array('messages' => 'Page Status is required!' ));
        $contentStatus->addMultiOption('published', 'Published');
        $contentStatus->addMultiOption('draft', 'Draft');
        $contentStatus->addMultiOption('disabled', 'Disabled');
        
        // content_order
        $contentOrder = $form->createElement('text', 'content_order');
        $contentOrder->setLabel($translate->translate('Field_Content_Order'));
        //$contentPage->addValidator('Int', true, array('messages' => 'Numbers only for the Display Order!' ));
        $contentOrder->setDescription($translate->translate('Field_Content_Order_Description'));
        $contentOrder->setValue('0');

        // content_allow_comment
        $contentAllowComment = $form->createElement('Checkbox', 'content_allow_comment'); 
        $contentAllowComment->setLabel($translate->translate('Field_Content_Allow_Comment'));
        $contentAllowComment->setDescription($translate->translate('Field_Content_Allow_Comment_Description'));


        // content_description_title
        $contentDescriptionTitle = $form->createElement('text', 'content_description_title');
        $contentDescriptionTitle->setRequired(true);
        $contentDescriptionTitle->setLabel($translate->translate('Field_Content_Description_Title'));
        $contentDescriptionTitle->addValidator('NotEmpty', true, array('messages' => 'Page Title is required!' ));
        $contentDescriptionTitle->addValidator('stringLength', true, array(2, 250,'messages' => '4 to 250  characters only for the Page Title!'));
       
        

        // content_description_text
        //$contentDescriptionText = $form->createElement('textarea', 'content_description_text');
        $contentDescriptionText = $form->createElement('editor', 'content_description_text', array(
            'plugins'            => array('undo', '|', 'bold', 'italic'),
            'editActionInterval' => 2,
            'focusOnLoad'        => false,
            'height'             => '250px',
            'inheritWidth'       => true,
            'styleSheets'        => array('/js/dijit/themes/tundra/Editor.css'),
        ));

        $contentDescriptionText->setRequired(true);
        $contentDescriptionText->setLabel($translate->translate('Field_Content_Description_Text'));
        $contentDescriptionText->addValidator('NotEmpty', true, array('messages' => 'Page Content is required!' ));
        
        
       
        

        // content_description_meta_title 
        $contentDescriptionMetaTitle = $form->createElement('text', 'content_description_meta_title');
        $contentDescriptionMetaTitle->setLabel($translate->translate('Field_Content_Description_Meta_Title'));
        $contentDescriptionMetaTitle->addValidator('stringLength', true, array(2, 250,'messages' => '2 to 250  characters only for the Meta Title!'));

        // content_description_meta_description
        $contentDescriptionMetaDescription = $form->createElement('textarea', 'content_description_meta_description');
        $contentDescriptionMetaDescription->setLabel($translate->translate('Field_Content_Meta_Description'));

        // content_description_keyword
        $contentDescriptionKeyword = $form->createElement('textarea', 'content_description_keyword');
        $contentDescriptionKeyword->setLabel($translate->translate('Field_Content_Description_Keyword'));

        // Submit
        $submit = $form->createElement('submit', 'submit');
        $submit->setAttrib('class','formSubmit');        
		$submit->setLabel($translate->translate('Field_Content_Submit'));

        // CSR
        $csr = $form->createElement('hash', 'no_csr', array('salt' => 'unique'));
		$csr->addDecorators(array(array('ViewHelper'),
            array('Errors'),
            array('HtmlTag', array('tag' => 'p')),
            array('Label',   array('tag' => 'label')),
            array('Description', array('tag' => 'p')),
            )
        );

       
        $form->addElement($contentPage)
                ->addElement($contentInMenu)
                ->addElement($contentProtected)
                ->addElement($contentParentPage)
                ->addElement($contentLayout)
                ->addElement($contentStatus)
                ->addElement($contentOrder)
                ->addElement($contentAllowComment)
                ->addElement($contentDescriptionTitle)
                ->addElement($contentDescriptionText)
                ->addElement($contentDescriptionMetaTitle)
                ->addElement($contentDescriptionMetaDescription)
                ->addElement($contentDescriptionKeyword)
                ->addElement($submit)
                ->addElement($csr);

        return $form;
    }

}
