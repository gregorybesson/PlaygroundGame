<?php

namespace PlaygroundGame\Form\Frontend;

use Zend\InputFilter\InputFilter;

class ShareMailFilter extends InputFilter
{
    public function __construct()
    {

        $this->add(array(
            'name'       => 'email1',
            'required'   => false,
            'filters' => array(
                array('name' => 'Zend\Filter\StringTrim'),
            ),
            'validators' => array(
                array('name' => 'Zend\Validator\EmailAddress'),
            ),
        ));

        $this->add(array(
                'name'       => 'email2',
                'required'   => false,
                'filters' => array(
                        array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                        array('name' => 'Zend\Validator\EmailAddress'),
                ),
        ));

        $this->add(array(
                'name'       => 'email3',
                'required'   => false,
                'filters' => array(
                        array('name' => 'Zend\Filter\StringTrim'),
                ),
                'validators' => array(
                        array('name' => 'Zend\Validator\EmailAddress'),
                ),
        ));
    }
}
