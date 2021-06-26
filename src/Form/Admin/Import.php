<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class Import extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'slug',
            'options' => array(
                'label' => $translator->translate('Slug', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Slug', 'playgroundgame'),
            ),
        ));
        
        $this->add(array(
            'name' => 'import_file',
            'options' => array(
            'label' => $translator->translate('Import game', 'playgroundgame'),
            ),
            'attributes' => array(
            'type' => 'file',
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
