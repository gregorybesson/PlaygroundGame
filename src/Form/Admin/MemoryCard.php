<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class MemoryCard extends ProvidesEventsForm
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'name' => 'memory_id',
            'type'  => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'id',
            'type'  => 'Laminas\Form\Element\Hidden',
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
                'id' => 'title',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'description',
            'options' => array(
                'label' => $translator->translate('Description', 'playgroundgame'),
            ),
            'required' => false,
                'cols' => '40',
                'rows' => '10',
                'id' => 'description',
        ));

        // Adding an empty upload field to be able to correctly handle this on the service side.
        $this->add(array(
                'name' => 'upload_image',
                'attributes' => array(
                    'type'  => 'file',
                ),
                'options' => array(
                    'label' => $translator->translate('Image', 'playgroundgame'),
                    'label_attributes' => array(
                        'class' => 'control-label',
                    ),
                ),
        ));
        $this->add(array(
                'name' => 'image',
                'type'  => 'Laminas\Form\Element\Hidden',
                'attributes' => array(
                    'value' => '',
                ),
        ));
        $this->add(array(
                'name' => 'delete_image',
                'type' => 'Laminas\Form\Element\Hidden',
                'attributes' => array(
                    'value' => '',
                    'class' => 'delete_image',
                ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'jsonData',
            'options' => array(
                'label' => $translator->translate('Json Data', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
            'attributes' => array(
                'required' => false,
                'cols' => '40',
                'rows' => '10',
                'id' => 'jsonData',
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }


    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     *
     * @return InstantWinOccurrence
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
