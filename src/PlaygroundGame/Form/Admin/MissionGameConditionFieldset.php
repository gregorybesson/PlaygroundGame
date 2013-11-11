<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Entity\MissionGameCondition;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class MissionGameConditionFieldset extends Fieldset
{
    public function __construct($name = null,ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\MissionGameCondition'))
        ->setObject(new MissionGameCondition());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
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
            'type' => 'Zend\Form\Element\Select',
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
