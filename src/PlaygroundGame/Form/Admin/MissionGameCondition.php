<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;
use PlaygroundGame\Entity\MissionGameCondition as MissionGameConditionEntity;

class MissionGameCondition extends ProvidesEventsForm
{
    /**
    * @var Zend\ServiceManager\ServiceManager $serviceManager
    */
    protected $serviceManager;
    protected $mission;

    /**
    * __construct : permet de construire le formulaire qui gerera l'entity MissionGameCondition
    *
    * @param string $name
    * @param Zend\ServiceManager\ServiceManager $serviceManager 
    * @param Zend\I18n\Translator\Translator $translator
    *
    */
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id__index__'
        ));

        $games = $this->getGames($serviceManager);
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'games__index__',
            'options' => array(
                'value_options' => $games,
                'label' => $translator->translate('game', 'playgroundGame')
            )
        ));

        $conditions = $this->getConditions();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'conditions__index__',
            'options' => array(
                'value_options' => $conditions,
                'label' => $translator->translate('conditions', 'playgroundGame')
            )
        ));

        $this->add(array(
            'name' => 'points__index__',
            'options' => array(
                'label' => $translator->translate('points', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('points', 'playgroundgame'),
            ),
        ));

    }


     /**
     * retrieve all games for associate to a missionGame
     * @param ServiceManager $serviceManager
     *
     * @return array $gamesArray
     */
    public function getGames($serviceManager)
    {
        $gamesArray = array();
        $gameService = $serviceManager->get('playgroundgame_game_service');
        $games = $gameService->getGameMapper()->findAll();
    
        foreach ($games as $game) {
            $gamesArray[$game->getId()] = $game->getTitle();
        }

        return $gamesArray;
    }


    /**
     * retrieve all conditions 
     *
     * @return array $conditions
     */
    public function getConditions()
    {
        return MissionGameConditionEntity::$conditions;
    }
}
