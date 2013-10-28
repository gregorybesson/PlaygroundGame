<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class PostVote extends Game
{

    public function __construct($name = null, ServiceManager $sm, Translator $translator)
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
            'name' => 'template',
            'attributes' =>  array(
                'id' => 'template',
                'options' => array(
                    'text' => $translator->translate('Text', 'playgroundgame'),
                    'photo' => $translator->translate('Photo', 'playgroundgame'),
                    'recipe' => $translator->translate('Recipe', 'playgroundgame'),
                ),
            ),
            'options' => array(
                'empty_option' => $translator->translate('What is the posts type ?', 'playgroundgame'),
                'label' => $translator->translate('Post type', 'playgroundgame'),
            ),
        ));

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
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'voteAnonymous',
            'options' => array(
                'label' => $translator->translate('Allow anonymous visitors to vote', 'playgroundgame'),
            ),
        ));
    }
}
