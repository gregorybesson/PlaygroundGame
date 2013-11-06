<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class InstantWin extends Game
{
    public function __construct($name = null, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // Mapping of an Entity to get value by getId()... Should be taken in charge by Doctrine Hydrator Strategy...
        // having to fix a DoctrineModule bug :( https://github.com/doctrine/DoctrineModule/issues/180
        // so i've extended DoctrineHydrator ...
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\InstantWin');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'occurrenceType',
            'attributes' =>  array(
                'id' => 'occurrenceType',
                'options' => array(
                    'datetime' => $translator->translate('Date', 'playgroundgame'),
                    'code' => $translator->translate('Code', 'playgroundgame'),
                    // 'visitor' => $translator->translate('Visitor', 'playgroundgame'),
                    // 'random' => $translator->translate('Random', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Type d\'instant gagnant', 'playgroundgame'),
                // 'empty_option' => $translator->translate('Type d\'instant gagnant', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'scheduleOccurrenceAuto',
            'attributes' =>  array(
                'id' => 'scheduleOccurrenceAuto',
                'options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Auto IG Generation', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'occurrenceNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number occurences', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Number occurences', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'winningOccurrenceNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number winning occurences', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Number winning occurences', 'playgroundgame'),
                'desc' => $translator->translate('Only if you don\'t want all the
                 scheduled occurrences to be winning', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'occurrenceValueMask',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Value mask', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Value mask', 'playgroundgame'),
                'desc' => $translator->translate('Only for "Code" instant win', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'occurrenceValueSize',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number of characters', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Value size', 'playgroundgame'),
                'desc' => $translator->translate('Only for "Code" instant win', 'playgroundgame'),
            ),
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Select',
        		'name' => 'occurrenceDrawFrequency',
        		'attributes' =>  array(
        				'id' => 'occurrenceDrawFrequency',
        				'options' => array(
        						'hour' => $translator->translate('Hour', 'playgroundgame'),
        						'day' => $translator->translate('Day', 'playgroundgame'),
        						'week' => $translator->translate('Week', 'playgroundgame'),
        						'month' => $translator->translate('Month', 'playgroundgame'),
        						'game' => $translator->translate('Game', 'playgroundgame'),
        				),
        		),
        		'options' => array(
        				'empty_option' => $translator->translate('Instant win creation frequency ?', 'playgroundgame'),
        				'label' => $translator->translate('Creation frequency', 'playgroundgame'),
        		),
        ));

        // Adding an empty upload field to be able to correctly handle this on
        // the service side.
        $this->add(array(
                'name' => 'uploadScratchcardImage',
                'attributes' => array(
                    'type' => 'file'
                ),
                'options' => array(
                    'label' => $translator->translate('Game Image scraping', 'playgroundgame')
                )
        ));
        $this->add(array(
                'name' => 'scratchcardImage',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'value' => ''
                )
        ));
    }
}
