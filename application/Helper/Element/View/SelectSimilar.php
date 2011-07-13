<?php

class Helper_Element_View_SelectSimilar extends Zend_Dojo_View_Helper_Dijit
{
     /**
     * Dijit being used
     * @var string
     */
    protected $_dijit  = 'dijit.form.Textarea';

    /**
     * HTML element type
     * @var string
     */
    protected $_elementType = 'text';

    /**
     * Dojo module to use
     * @var string
     */
    protected $_module = 'dijit.form.Textarea';

    /**
     * dijit.form.Textarea
     * 
     * @param  int $id 
     * @param  mixed $value 
     * @param  array $params  Parameters to use for dijit creation
     * @param  array $attribs HTML attributes
     * @return string
     */
    public function SelectSimilar($id, $value = null, array $params = array(), array $attribs = array())
    {        

        $valueArray = unserialize($value);

    
        $newValue = '';

        foreach($valueArray['similar'] as $values)
        {
            $newValue .=  $values['name'] . ', ';
        }

        

        if (!array_key_exists('id', $attribs)) {
            $attribs['id']    = $id;
        }
        $attribs['name']  = $id;
        $attribs['type']  = $this->_elementType;

        $attribs = $this->_prepareDijit($attribs, $params, 'textarea');

        $html = '<textarea' . $this->_htmlAttribs($attribs) . '>'
              . $newValue
              . "</textarea>\n";

        return $html;
    }

}
