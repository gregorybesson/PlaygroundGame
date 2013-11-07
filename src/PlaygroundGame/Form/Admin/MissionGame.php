<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class MissionGame extends ProvidesEventsForm
{
    /**
    * __construct : permet de construire le formulaire qui peuplera l'entity LeaderboardType
    *
    * @param string $name
    * @param Zend\ServiceManager\ServiceManager $serviceManager 
    * @param Zend\I18n\Translator\Translator $translator
    *
    */
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $missionGameCondition = new MissionGameCondition(null, $serviceManager, $translator);
        $this->add(array(
            'type'    => 'Zend\Form\Element\Collection',
            'name'    => 'missionGameCondition',
            'options' => array(
                'id'    => 'missionGameCondition',
                'label' => $translator->translate('List of MissionGameCondition', 'playgroundgame'),
                'count' => 0,
                'should_create_template' => true,
                'allow_add' => true,
                'allow_remove' => true,
                'target_element' => $missionGameCondition
            )
        ));

    
        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array('type'  => 'submit'));

        $this->add($submitElement, array('priority' => -100));
    }
}
