<?php

namespace PlaygroundGame\Form\Frontend;

use Zend\InputFilter\InputFilter;

class InstantWinOccurrenceCodeFilter extends InputFilter {

    public function __construct() {

        $this->add(array(
            'name' => 'code-input',
            'required' => true,
            'allow_empty' => false,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'encoding' => 'UTF-8',
                        'min' => 1,
                    ),
                ),
            ),
        ));
    }

}