<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Form;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\Form\Element;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;
use PlaygroundGame\Form\Admin\Game;

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
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'replayPuzzle',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame')
                ),
                'label' => $translator->translate('Go to the next puzzle only when the previous is won', 'playgroundgame')
            )
        ));

        $this->add(array(
        		'name' => 'timer',
        		'type' => 'Laminas\Form\Element\Radio',
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
        		'type' => 'Laminas\Form\Element\Text',
        		'attributes' => array(
        				'placeholder' => $translator->translate('Duration in seconds','playgroundgame'),
        		),
        		'options' => array(
        				'label' => $translator->translate('Timer Duration','playgroundgame'),
        		),
        ));
    }
}
