<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class Crossword extends Game
{
    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // having to fix a Doctrine-module bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\Crossword');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'gameType',
            'attributes' =>  array(
                'id' => 'gameType',
                'options' => array(
                    'crossword' => $translator->translate("A regular crossword", 'playgroundgame'),
                    'word_search' => $translator->translate("A wordsearch puzzle", 'playgroundgame'),
                ),
            ),
            'options' => array(
                'label' => $translator->translate('Crossword type', 'playgroundgame'),
                // 'empty_option' => $translator->translate('Type d\'instant gagnant', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'victoryConditions',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('% good picks vs mistakes', 'playgroundgame'),
                'id' => 'victoryConditions',
            ),
            'options' => array(
                'label' => $translator->translate('Victory conditions', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'layoutColumns',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number of columns in the crossword', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s the number of columns in the crossword ?', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'layoutRows',
            'type' => 'Laminas\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number of rows in the crossword', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('What\'s the number of rows in the crossword ?', 'playgroundgame'),
            ),
        ));
    }
}
