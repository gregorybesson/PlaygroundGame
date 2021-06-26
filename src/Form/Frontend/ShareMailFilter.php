<?php

namespace PlaygroundGame\Form\Frontend;

use Laminas\InputFilter\InputFilter;

class ShareMailFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'       => 'email1',
            'required'   => false,
            'filters' => array(
                array('name' => 'Laminas\Filter\StringTrim'),
            ),
            'validators' => array(
                array('name' => 'Laminas\Validator\EmailAddress'),
            ),
        ));

        $this->add(array(
                'name'       => 'email2',
                'required'   => false,
                'filters' => array(
                        array('name' => 'Laminas\Filter\StringTrim'),
                ),
                'validators' => array(
                        array('name' => 'Laminas\Validator\EmailAddress'),
                ),
        ));

        $this->add(array(
                'name'       => 'email3',
                'required'   => false,
                'filters' => array(
                        array('name' => 'Laminas\Filter\StringTrim'),
                ),
                'validators' => array(
                        array('name' => 'Laminas\Validator\EmailAddress'),
                ),
        ));
        
        $this->add(array(
            'name'       => 'email4',
            'required'   => false,
            'filters' => array(
                array('name' => 'Laminas\Filter\StringTrim'),
            ),
            'validators' => array(
                array('name' => 'Laminas\Validator\EmailAddress'),
            ),
        ));
        
        $this->add(array(
            'name'       => 'email5',
            'required'   => false,
            'filters' => array(
                array('name' => 'Laminas\Filter\StringTrim'),
            ),
            'validators' => array(
                array('name' => 'Laminas\Validator\EmailAddress'),
            ),
        ));
    }
}
