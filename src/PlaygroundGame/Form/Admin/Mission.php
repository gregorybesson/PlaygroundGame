<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class Mission extends ProvidesEventsForm
{
    /**
    * @var Zend\ServiceManager\ServiceManager $serviceManager
    */
    protected $serviceManager;

    /**
    * __construct : permet de construire le formulaire qui peuplera l'entity LeaderboardType
    *
    * @param string $name
    * @param Zend\ServiceManager\ServiceManager $serviceManager 
    * @param Zend\I18n\Translator\Translator $translator
    *
    */
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $this->setAttribute('enctype','multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => $translator->translate('title', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('title', 'playgroundgame'),
                'required' => 'required'
            ),
            'validator' => array(
                new \Zend\Validator\NotEmpty(),
            )
        ));

        $this->add(array(
            'name' => 'uploadImage',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => $translator->translate('image', 'playgroundgame'),
            ),
        ));
        $this->add(array(
            'name' => 'image',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                    'value' => '',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'description',
            'options' => array(
                'label' => $translator->translate('description', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '2',
                'id' => 'description',
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'hidden',
            'options' => array(
                'label' => $translator->translate('cache', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array('type'  => 'submit'));

        $this->add($submitElement, array('priority' => -100));
    }
}
