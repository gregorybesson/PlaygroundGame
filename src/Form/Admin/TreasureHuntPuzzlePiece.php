<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Form;
use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;

class TreasureHuntPuzzlePiece extends ProvidesEventsForm
{
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->setServiceManager($serviceManager);

        $this->add(array(
          'name' => 'puzzle_id',
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
            'label' => $translator->translate('Title', 'playgroundgame')
          ),
          'attributes' => array(
            'type' => 'text',
            'placeholder' => $translator->translate('Title', 'playgroundgame')
          )
        ));

        $this->add(array(
          'type' => 'Laminas\Form\Element\Textarea',
          'name' => 'hint',
          'options' => array(
            'label' => $translator->translate('Indice', 'playgroundgame'),
            'label_attributes' => array(
              'class' => 'control-label',
            ),
          ),
          'attributes' => array(
            'required' => false,
            'cols' => '10',
            'rows' => '2',
            'id' => 'question',
          ),
        ));

        $this->add(array(
        		'type' => 'Laminas\Form\Element\Textarea',
        		'name' => 'area',
        		'options' => array(
        				'label' => $translator->translate('Area', 'playgroundgame'),
        				'label_attributes' => array(
        					'class' => 'control-label',
        				),
        		),
        		'attributes' => array(
        				'required' => false,
        				'cols' => '40',
        				'rows' => '8',
        				'id' => 'area',
        		),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'timer',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('Non', 'playgroundgame'),
                    '1' => $translator->translate('Oui', 'playgroundgame')
                ),
                'label' => $translator->translate('Timer', 'playgroundgame')
            )
        ));

        $this->add(array(
        		'name' => 'timer_duration',
        		'options' => array(
        				'label' => $translator->translate('Durée du chrono', 'playgroundgame'),
        				'label_attributes' => array(
        						'class' => 'control-label',
        				),
        		),
        ));

        $this->add(array(
        		'name' => 'position',
        		'options' => array(
        				'label' => $translator->translate('Position', 'playgroundgame'),
        				'label_attributes' => array(
        					'class' => 'control-label',
        				),
        		),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
        ->setAttributes(array(
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
    public function getServiceManager ()
    {
    	return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
    	$this->serviceManager = $serviceManager;

    	return $this;
    }
}
