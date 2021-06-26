<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Mvc\I18n\Translator;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;

class Quiz extends Game
{
    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // Mapping of an Entity to get value by getId()... Should be taken in charge by Doctrine Hydrator Strategy...
        // having to fix a DoctrineModule bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\Quiz');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'drawAuto',
            'attributes' =>  array(
                'id' => 'drawAuto',
                'options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Automatic drawing out', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'winners',
            'options' => array(
                'label' => $translator->translate('Winners number', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Winners number', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'substitutes',
            'options' => array(
                'label' => $translator->translate('Substitutes number', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Substitutes number', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'displayStats',
            'attributes' =>  array(
                'id' => 'displayStats',
                'options' => array(
                    'never' => $translator->translate('Never', 'playgroundgame'),
                    'entry' => $translator->translate('After each entry', 'playgroundgame'),
                    'game' => $translator->translate('At the end of the game', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Display statistics', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'displayGoodAnswers',
            'attributes' =>  array(
                'id' => 'displayGoodAnswers',
                'options' => array(
                    'never' => $translator->translate('Never', 'playgroundgame'),
                    'question' => $translator->translate('After each question', 'playgroundgame'),
                    'entry' => $translator->translate('After each entry', 'playgroundgame'),
                    'game' => $translator->translate('At the end of the game', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Display the answers', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'timer',
            'type' => 'Laminas\Form\Element\Radio',
            'attributes' => array(
                'required' => 'required',
                'value' => '0',
            ),
            'options' => array(
                'label' => $translator->translate('Use a Timer', 'playgroundgame'),
                'value_options' => array(
                    '0' => 'No',
                    '1' => 'yes',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'timerDuration',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Duration in seconds', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Timer Duration', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'victoryConditions',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('% good answers', 'playgroundgame'),
                'id' => 'victoryConditions',
            ),
            'options' => array(
                'label' => $translator->translate('Victory conditions', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'questionGrouping',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'required' => 'required',
            ),
            'options' => array(
                'label' => $translator->translate('Group question', 'playgroundgame'),
            ),
        ));
    }
}
