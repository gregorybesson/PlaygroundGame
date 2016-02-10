<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class TradingCardModel extends ProvidesEventsForm
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'name' => 'trading_card_id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

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
                'id' => 'title'
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'options' => array(
                'label' => $translator->translate('Type (Collector / Standard)', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'type'
            ),
        ));

        $this->add(array(
            'name' => 'family',
            'options' => array(
                'label' => $translator->translate('Family', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'family'
            ),
        ));

        $this->add(array(
            'name' => 'points',
            'options' => array(
                'label' => $translator->translate('Points', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'points'
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'description',
            'options' => array(
                'label' => $translator->translate('Description', 'playgroundgame'),
            ),
            'required' => false,
                'cols' => '40',
                'rows' => '10',
                'id' => 'description',
        ));

        $this->add(array(
            'name' => 'distribution',
            'options' => array(
                'label' => $translator->translate('probability drawing', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'distribution'
            ),
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
                'type'  => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'value' => '',
                ),
        ));
        $this->add(array(
                'name' => 'delete_image',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'value' => '',
                    'class' => 'delete_image',
                ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\DateTime',
                'name' => 'availability',
                'options' => array(
                    'label' => $translator->translate('Availability date', 'playgroundgame'),
                    'format' => 'd/m/Y H:i:s'
                ),
                'attributes' => array(
                    'type' => 'text',
                    'class'=> 'datepicker',
                    'id' => 'availability'
                ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
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
     * @return InstantWinOccurrence
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
