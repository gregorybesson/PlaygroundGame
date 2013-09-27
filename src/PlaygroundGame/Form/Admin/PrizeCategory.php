<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class PrizeCategory extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        parent::__construct();
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
                'label' => $translator->translate('Title', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Title', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'identifier',
            'options' => array(
                'label' => $translator->translate('Slug', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
                'label' => $translator->translate('Active', 'playgroundgame'),
            ),
        ));

        // Adding an empty upload field to be able to correctly handle this on the service side.
        $this->add(array(
                'name' => 'upload_picto',
                'attributes' => array(
                        'type'  => 'file',
                ),
                'options' => array(
                        'label' => $translator->translate('Picto', 'playgroundgame'),
                ),
        ));
        $this->add(array(
                'name' => 'picto',
                'type'  => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                        'value' => '',
                ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
        ->setAttributes(array(
            'type'  => 'submit',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

    }
}
