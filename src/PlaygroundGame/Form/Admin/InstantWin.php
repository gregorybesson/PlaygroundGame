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

        /*$this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'occurrenceType',
            'attributes' =>  array(
                'id' => 'occurrenceType',
                'options' => array(
                    'datetime' => $translator->translate('Date', 'playgroundgame'),
                    'visitor' => $translator->translate('Visitor', 'playgroundgame'),
                    'random' => $translator->translate('Random', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Type d\'instant gagnant', 'playgroundgame'),
                'label' => $translator->translate('Type d\'instant gagnant', 'playgroundgame'),
            ),
        ));*/

        $this->add(array(
            'name' => 'occurrenceType',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 'random'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'scheduleOccurrenceAuto',
            'attributes' =>  array(
                'id' => 'scheduleOccurrenceAuto',
                'options' => array(
                    '0' => $translator->translate('Non', 'playgroundgame'),
                    '1' => $translator->translate('Oui', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Génération des IG automatique', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'occurrenceNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => 'Nombre d\'instants gagnants',
            ),
            'options' => array(
                'label' => 'Nombre d\'instants gagnants',
            ),
        ));
        
        $this->add(array(
        		'type' => 'Zend\Form\Element\Select',
        		'name' => 'occurrenceDrawFrequency',
        		'attributes' =>  array(
        				'id' => 'occurrenceDrawFrequency',
        				'options' => array(
        						'hour' => $translator->translate('heure', 'playgroundgame'),
        						'day' => $translator->translate('Jour', 'playgroundgame'),
        						'week' => $translator->translate('Semaine', 'playgroundgame'),
        						'month' => $translator->translate('Mois', 'playgroundgame'),
        						'game' => $translator->translate('Jeu', 'playgroundgame'),
        				),
        		),
        		'options' => array(
        				'empty_option' => $translator->translate('Création des instants gagnants sur quelle fréquence ?', 'playgroundgame'),
        				'label' => $translator->translate('Fréquence de création', 'playgroundgame'),
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
                    'label' => $translator->translate('Image de grattage du jeu', 'playgroundgame')
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
