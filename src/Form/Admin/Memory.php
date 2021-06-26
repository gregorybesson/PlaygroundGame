<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class Memory extends Game
{
    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // having to fix a Doctrine-module bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\Memory');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

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

        // Adding an empty upload field to be able to correctly handle this on
        // the service side.
        $this->add(array(
            'name' => 'uploadBackImage',
            'attributes' => array(
                'type' => 'file',
            ),
            'options' => array(
                'label' => $translator->translate('Back card image', 'playgroundgame'),
            ),
        ));
        $this->add(array(
                'name' => 'backImage',
                'type' => 'Laminas\Form\Element\Hidden',
                'attributes' => array(
                    'value' => '',
                ),
        ));
        $this->add(array(
            'name' => 'deleteBackImage',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_scratch_image',
            ),
        ));
    }
}
