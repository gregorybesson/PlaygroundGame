<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Entity\MissionGameCondition;
use Laminas\Form\Fieldset;
use Laminas\Mvc\I18n\Translator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;

class MissionGameConditionFieldset extends Fieldset
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\MissionGameCondition'))
        ->setObject(new MissionGameCondition());

        $this->add(array(
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'id',
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'attribute',
            'options' => array(
                'empty_option' => $translator->translate('Select the attribute', 'playgroundgame'),
                'value_options' => array(
                    'winner' => $translator->translate('status of previous game', 'playgroundgame'),
                    'points' => $translator->translate('Points of previous game', 'playgroundgame'),
                ),
                'label' => $translator->translate('Attribute', 'playgroundgame'),
            ),
        ));
        
        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'comparison',
            'options' => array(
                'empty_option' => $translator->translate('Type of comparison', 'playgroundgame'),
                'value_options' => array(
                    '==' => $translator->translate('equals', 'playgroundgame'),
                    '>' => $translator->translate('more than', 'playgroundgame'),
                    '<' => $translator->translate('less than', 'playgroundgame'),
                ),
                'label' => $translator->translate('Comparison', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'value',
            'options' => array(
                'label' => $translator->translate('Value'),
            ),
        ));
    }
}
