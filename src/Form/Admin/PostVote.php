<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class PostVote extends Game
{
    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // Mapping of an Entity to get value by getId()... Should be taken in charge by Doctrine Hydrator Strategy...
        // having to fix a DoctrineModule bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\PostVote');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'postDisplayMode',
            'attributes' =>  array(
                'id' => 'postDisplayMode',
                'options' => array(
                    'date' => $translator->translate('Date', 'playgroundgame'),
                    'vote' => $translator->translate('Vote number', 'playgroundgame'),
                    'random' => $translator->translate('Random', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('Display posts orber by', 'playgroundgame'),
                'label' => $translator->translate('Posts display mode', 'playgroundgame'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'postDisplayNumber',
            'attributes' =>  array(
                'id' => 'postDisplayNumber',
            ),
            'options' => array(
                'empty_option' => $translator->translate('Number of displayed posts', 'playgroundgame'),
                'label' => $translator->translate('Number of displayed posts', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'voteAnonymous',
            'options' => array(
                'label' => $translator->translate('Allow anonymous visitors to vote', 'playgroundgame'),
            ),
        ));

        $this->add(array(
                'type' => 'Zend\Form\Element\Select',
                'name' => 'moderationType',
                'attributes' =>  array(
                        'id' => 'moderationType',
                        'options' => array(
                                '0' => $translator->translate('Post moderation', 'playgroundgame'),
                                '1' => $translator->translate('Pre moderation', 'playgroundgame'),
                        ),
                ),
                'options' => array(
                        'empty_option' => $translator->translate('Moderation type', 'playgroundgame'),
                        'label' => $translator->translate('Moderation type', 'playgroundgame'),
                ),
        ));
    }
}
