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
 * @subpackage Form
 * @copyright  Copyright (c) 2005-2009 VooDoo Music Box. (http://www.VoodooMusicBox.com)
 * @license    http://www.VoodooMusicBox.com/license/new-bsd     New BSD License
 * @version    $Id: Form.php 4 2009-6-1 Jaimie $
 */
class Model_Artist_Form
{
    /* @access Public
     * @var object
     */
    private static $form         = null;

    /* @access Public
     * @var object
     */
    private static $translate    = null;

    /* @access Public
     * @var object
     */
    private static $config      = null;

    
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

        self::$config = Zend_Registry::get('configuration');
    }

    
     /** 
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function editForm($data)
    {
        Zend_Dojo::enableForm(self::$form);

        self::$form->setAction('artist/edit/'.urlencode($data['artist']))
            ->setMethod('post')
            ->setAttrib('id', 'edit-artist')
            ->setAttrib('name', 'edit-artist');

        // artist
        $artist = self::$form->createElement('TextBox', 'artist')
            ->setRequired(true)
            ->setAttrib('size',50)
            ->setPropercase(true) 
            ->setTrim(true)
            ->setMaxLength(200)
            ->setLabel('Field_Artist_Artist')
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Artist_Artist_NotEmpty'))
            ->setValue($data['artist']);

        // date_added
        $dateAdded = self::$form->createElement('DateTextBox', 'date_added')
            ->setRequired(true)
            ->setLabel('Field_Artist_Date_Added')
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Artist_Date_Added_NotEmpty'))
            ->setValue(date('Y-m-d', $data['date_added']));
      
        // genre
        $genre = self::$form->createElement('selectGenre', 'genre')
            ->setLabel('Field_Artist_Genre')
            ->setValue($data['genres']);

        // similar_artists
        $similarArtists = self::$form->createElement('selectSimilar', 'similar_artists')
            ->setLabel('Field_Artist_Similar_Artists')
            ->setValue($data['similar_artists']);

         // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Artist_Edit_Submit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($artist)
            ->addElement($dateAdded)
            ->addElement($genre)
            ->addElement($similarArtists) 
            ->addElement($csr)
            ->addElement($submit);

        self::$form->addDisplayGroup(array( 'artist', 'date_added'), 'artist-info', array('legend' => 'Form_Legend_Artist_Info', ) );

        self::$form->addDisplayGroup(array( 'genre' ), 'artist-genre', array('legend' => 'Form_Legend_Artist_Genre', ));

        self::$form->addDisplayGroup(array( 'similar_artists' ), 'artist-similar', array('legend' => 'Form_Legend_Artist_Similar_Artists', ));

        self::$form->addDisplayGroup(array( 'submit', 'no_csr'), 'update' );

        

        return self::$form;
    }


    /** 
     * @access Public
     * @param Int $imageId
     * @return Object
     */
    public function artistImageDelete($imageId)
    {
         self::$form->setAction('picture/delete/artist-image/id/'.$imageId)
            ->setMethod('post')
            ->setAttrib('id', 'delete-image')
            ->setAttrib('name', 'delete-image');
       
        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Picture_Delete_Image_Dubmit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($csr)
            ->addElement($submit);

        return self::$form;
    }


    /** 
     * @access Public
     * @param Array $data
     * @return Object
     */
    public function artistImageEdit($data)
    {
        self::$form->setAction('picture/edit/artist-image/id/' . $data['id'])
            ->setMethod('post')
            ->setAttrib('id', 'edit-image')
            ->setAttrib('name', 'edit-image');

        // title
        $title = self::$form->createElement('text', 'title')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Picture_Title_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 100, 'messages' => 'Error_Picture_Title_StringLength'))
            ->setAttrib('size',50)
            ->setLabel('Field_Picture_Title')
            ->setValue($data['title']);

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Picture_Edit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($title)
            ->addElement($csr)
            ->addElement($submit);

        return self::$form;
    }

    
    public function artistImageCreate($artistName, $guid)
    {
        /** get config for picture module */
        $moduleConfig = Zend_Registry::get('moduleConfig')->module->picture;

         self::$form->setAction('picture/add/artist-image/artist/'.urlencode($artistName))
            ->setMethod('post')
            ->setAttrib('id', 'add-image')
            ->setAttrib('name', 'add-image')
            ->setAttrib('enctype', 'multipart/form-data');

        // max
        $max = self::$form->createElement('hidden', 'MAX_FILE_SIZE')
            ->setValue('100000');

        // title
        $title = self::$form->createElement('text', 'title')
            ->setRequired(true)
            ->addValidator('NotEmpty', true, array('messages' => 'Error_Picture_Title_NotEmpty'))
            ->addValidator('StringLength', true, array(2, 100, 'messages' => 'Error_Picture_Title_StringLength'))
            ->setAttrib('size',50)
            ->setLabel('Field_Picture_Title');

        // image
        $imageDir = Zend_Registry::get('siteRootDir') . self::$config->media->imagePath .'/'. $guid; 
       
        /** if no directory create it */
        if( !is_dir($imageDir) ) {
            if(!mkdir($imageDir, 0755)) {
                throw new exception('Unable to create the directory: ' . $imageDi . '. Please check permisions');
            }
        }  

        $image = self::$form->createElement('file', 'image')
            ->setLabel('Field_Picture_Image')
            ->setDestination($imageDir)
            ->addValidator('IsImage', true, array('jpeg', 'messages' => 'Error_Picture_image_IsImage'))
            ->addValidator('ImageSize', true, array('minwidth' => $moduleConfig->minwidth, 'maxwidth' => $moduleConfig->maxwidth, 'minheight' => $moduleConfig->minheight, 'maxheight' => $moduleConfig->maxheight, 'messages' => 'Error_Picture_image_ImageSize'))
            ->addValidator('FilesSize', false, array('min' => $moduleConfig->minsize.'B', 'max' => $moduleConfig->maxsize.'B', 'messages' => 'Error_Picture_image_FilesSize'));

        // Submit
        $submit = self::$form->createElement('submit', 'submit' )
            ->setLabel('Field_Picture_Edit');     
		
        // CSR
        $csr = self::$form->createElement('hash', 'no_csr', array( 'salt' => 'unique'));

         self::$form
            ->addElement($max)
            ->addElement($title)
            ->addElement($image)
            ->addElement($csr)
            ->addElement($submit);

        return self::$form;

    }
}
