<?php

/** Zend_Dojo_Form_Element_Dijit */
require_once 'Zend/Dojo/Form/Element/Dijit.php';

class Element_SelectSimilar extends Zend_Dojo_Form_Element_Dijit
{

    /**
     * Use SimpleTextarea dijit view helper
     * @var string
     */
    public $helper = 'SelectSimilar';


    public function init()
    {
       $this->getView()->addHelperPath('Helper/Element/View', 'Helper_Element_View');
                  
    }



}

