<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Entity\MissionGame;
use Laminas\Form\Fieldset;
use Laminas\Mvc\I18n\Translator;
use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;
use Laminas\InputFilter\InputFilterProviderInterface;

class MissionGameFieldset extends Fieldset implements InputFilterProviderInterface
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\MissionGame'))
        ->setObject(new MissionGame());

        $this->add(array(
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'id',
        ));

        $this->add(array(
            'name' => 'game',
            'type' => 'DoctrineModule\Form\Element\ObjectSelect',
            'options' => array(
                'empty_option' => $translator->translate('Select a game', 'playgroundgame'),
                'label' => $translator->translate('Game', 'playgroundgame'),
                'object_manager' => $entityManager,
                'target_class' => 'PlaygroundGame\Entity\Game',
                'property' => 'title',
            ),
            'attributes' => array(
                'required' => false,
                //'multiple' => 'multiple',
            ),
        ));
        
        $this->add(array(
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'position',
            'attributes' =>  array(
                'id' => 'position__index__',
            ),
        ));
        
        $gameMissionConditionFieldset = new MissionGameConditionFieldset(null, $serviceManager, $translator);
        $this->add(array(
            'type'    => 'Laminas\Form\Element\Collection',
            'name'    => 'conditions',
            'options' => array(
                'id'    => 'conditions',
                'label' => $translator->translate('List of conditions', 'playgroundgame'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $gameMissionConditionFieldset,
            ),
        ));
        
        $this->add(array(
            'type' => 'Laminas\Form\Element\Button',
            'name' => 'add_condition',
            'options' => array(
                'label' => $translator->translate('Add Condition', 'playgroundgame'),
            ),
            'attributes' => array(
                'class' => 'addCondition',
            ),
        ));
    }
    
    public function getInputFilterSpecification()
    {
        return array('game' => array('required' => true));
    }
}
