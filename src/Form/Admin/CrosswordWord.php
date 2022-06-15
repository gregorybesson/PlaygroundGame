<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class CrosswordWord extends ProvidesEventsForm
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'name' => 'crossword_id',
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
            'name' => 'solution',
            'options' => array(
                'label' => $translator->translate('Solution', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'solution',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'clue',
            'options' => array(
                'label' => $translator->translate('Clue', 'playgroundgame'),
            ),
            'required' => false,
                'cols' => '40',
                'rows' => '10',
                'id' => 'clue',
        ));

        $this->add(array(
            'name' => 'layoutColumn',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Column position in the crossword', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s its column position in the crossword ?', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'layoutRow',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Row position in the crossword', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s its row position in the crossword ?', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'position',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Definition position in the crossword', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s its definition\'s position in the crossword ?', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'orientation',
            'attributes' =>  array(
                'id' => 'orientation',
                'options' => array(
                    'across' => $translator->translate('Horizontal', 'playgroundgame'),
                    'down' => $translator->translate('Vertical', 'playgroundgame'),
                ),
            ),
            'options' => array(
                    'empty_option' => $translator->translate('Orientation', 'playgroundgame'),
                    'label' => $translator->translate('Orientation in the crossword', 'playgroundgame'),
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
