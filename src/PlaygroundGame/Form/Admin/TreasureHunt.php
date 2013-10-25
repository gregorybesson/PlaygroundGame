<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class TreasureHunt extends Game
{
    public function __construct($name = null, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // having to fix a Doctrine-module bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\TreasureHunt');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
        		'name' => 'winners',
        		'options' => array(
        				'label' => $translator->translate('Winners number', 'playgroundgame')
        		),
        		'attributes' => array(
        				'type' => 'text',
        				'placeholder' => $translator->translate('Winners number', 'playgroundgame')
        		)
        ));
        
        $this->add(array(
        		'name' => 'timer',
        		'type' => 'Zend\Form\Element\Radio',
        		'attributes' => array(
        				'required' => 'required',
        				'value' => '0',
        		),
        		'options' => array(
        				'label' => 'Use a Timer',
        				'value_options' => array(
        						'0' => $translator->translate('No', 'playgroundgame'),
        						'1' => $translator->translate('yes', 'playgroundgame'),
        				),
        		),
        ));
        
        $this->add(array(
        		'name' => 'timerDuration',
        		'type' => 'Zend\Form\Element\Text',
        		'attributes' => array(
        				'placeholder' => $translator->translate('Duration in seconds','playgroundgame'),
        		),
        		'options' => array(
        				'label' => $translator->translate('Timer Duration','playgroundgame'),
        		),
        ));
        
        $this->add(array(
        	'type' => 'Zend\Form\Element\Select',
        	'name' => 'playerType',
       		'attributes' =>  array(
   				'id' => 'playerType',
       			'options' => array(
       				'all' => $translator->translate('All', 'playgroundgame'),
 					'prospect' => $translator->translate('Prospect', 'playgroundgame'),
        			'customer' => $translator->translate('Customer', 'playgroundgame'),
   				),
       		),
        	'options' => array(
        		'empty_option' => $translator->translate('What player type can participate', 'playgroundgame'),
       			'label' => $translator->translate('Player type who can participate', 'playgroundgame'),
       		),
        ));
    }
}
