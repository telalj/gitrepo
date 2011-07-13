<?php

class Helper_Decorator_CheckBox extends Zend_Form_Decorator_Abstract
{
    public function buildLabel()
    {
        $element = $this->getElement();

        if ( $element->hasErrors() ) {
            $label .= '<img src="images/icons/flag_16.gif" alt="form error"><span class="errors">';
        } elseif ($element->isRequired()) {
            $label = '<span class="required">* </span>';
        }

        if ($translator = $element->getTranslator()) {
            $label .= $translator->translate($element->getLabel());
        }
        

        return $element->getView()
                       ->formLabel($element->getName(), $label, array('escape' => false));
    }

    public function buildInput()
    {
        $element = $this->getElement();
        $helper  = $element->helper;

        if ($element->hasErrors() ) {
            $element->setAttrib('class', 'fieldErrors');
        } else {
            $element->setAttrib('class', 'field');
        }


        return $element->getView()->$helper(
            $element->getName(),
            $element->getValue(),
            $element->getAttribs(),
            $element->options
        );
    }

    public function buildErrors()
    {
        $element  = $this->getElement();
        $messages = $element->getMessages();
        if (empty($messages)) {
            return '';
        }
        return '<div class="errors">' .
               $element->getView()->formErrors($messages) . '</div>';
    }

    public function buildDescription()
    {
        $element = $this->getElement();
        $desc    = $element->getDescription();
        if (empty($desc)) {
            return '';
        }
        return '<div class="description">' . $desc . '</div>';
    }

    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }
        if (null === $element->getView()) {
            return $content;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $label     = $this->buildLabel();
        $input     = $this->buildInput();
        $errors    = $this->buildErrors();
        $desc      = $this->buildDescription();

        $output = '<p><div class="form element">'
                . '<div class="grid_1"> ' . $input . '</div>'
                . $label                
                . $errors
                . $desc
                . '</div></p>';

        switch ($placement) {
            case (self::PREPEND):
                return $output . $separator . $content;
            case (self::APPEND):
            default:
                return $content . $separator . $output;
        }
    }
}

