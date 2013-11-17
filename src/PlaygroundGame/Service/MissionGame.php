<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundGame\Entity\Mission as MissionEntity;
use PlaygroundGame\Entity\MissionGame as MissionGameEntity;
use PlaygroundGame\Entity\MissionGameCondition as MissionGameConditionEntity;

class MissionGame extends EventProvider implements ServiceManagerAwareInterface
{

    /**
    * @var missionMapper
    */
    protected $missionMapper;
     /**
    * @var missionGameMapper
    */
    protected $missionGameMapper;
     /**
    * @var missionGameConditionMapper
    */
    protected $missionGameConditionMapper;
     /**
    * @var gameMapper
    */
    protected $gameMapper;
     /**
    * @var options
    */
    protected $options;


    public function checkGames($dataGames)
    {

        for ($i=0; $i < count($dataGames); $i++) { 
            if(!empty($dataGames[$i+1])){
                $game1 = $this->getGameMapper()->findById($dataGames[$i]['games']); 
                $game2 = $this->getGameMapper()->findById($dataGames[$i+1]['games']); 

                if ($game2->getEndDate() == null) {
                    continue;
                }

                // Si la date de fin du jeu 2 est inférieur a la date du jeu 1
                if($game2->getEndDate()->getTimestamp() < $game1->getStartDate()->getTimestamp()){            
                    return false;
                }
            }   
        }

        return true;
    }


    public function checkGamesInMission($dataGames)
    {
        $gamesId = array();
        for ($i=0; $i < count($dataGames); $i++) { 
            $gamesId[] = $dataGames[$i]['games']; 
        }        

        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $query = $em->createQuery('SELECT mg 
                                   FROM PlaygroundGame\Entity\MissionGame mg
                                   WHERE mg.game IN (:gamesId)'
        );
        $query->setParameter('gamesId', $gamesId);
        $games = $query->getResult();

        if(count($games) > 0) {
            return false;
        }

        return true;
    }
    /**
    * associate : Permet d'associer des jeux et des conditions à une mission
    * @param array $data 
    * @param Mission $mission
    *
    * @return MissionGameEntity $missionGameEntity
    */
    public function associate($data, $mission)
    {
        $missionGameEntity = new MissionGameEntity();
        $game = $this->getGameMapper()->findById($data['games']);
        $missionGameEntity->setGame($game);
        $missionGameEntity->setPosition($data['position']);
        $missionGameEntity->setMission($mission);
        $missionGameEntity = $this->getMissionGameMapper()->insert($missionGameEntity); 

        $missionGameConditionEntity = new MissionGameConditionEntity;
        $missionGameConditionEntity->setMissionGame($missionGameEntity);
        $missionGameConditionEntity->setAttribute($data['conditions']);
        $missionGameConditionEntity->setValue($data['points']);
        $missionGameConditionEntity = $this->getMissionGameConditionMapper()->insert($missionGameConditionEntity); 

        return $missionGameEntity;
    }

    /**
    * clear : Permet de supprimer l'association des jeux et des conditions à une mission
    * @param Mission $mission
    */
    public function clear($mission)
    {
        $missionGames = $this->findMissionGameByMission($mission);
        foreach ($missionGames as $missionGames) {
            $this->getMissionGameMapper()->remove($missionGames); 
        }
    }


    public function checkCondition($game, $winner, $prediction, $entry)
    {
        $missionGame = $this->findMissionGameByGame($game);
        if(empty($missionGame)){
            return false;
        }

        if($missionGame->getMission()->getActive() == false){
            return false;
        }

        $nextMissionGames = $this->getMissionGameMapper()->findBy(array('mission'=> $missionGame->getMission(), 'position'=> ($missionGame->getPosition() + 1 )));
        
        if(empty($nextMissionGames)){
            return false;
        }

        $nextMissionGame = $nextMissionGames[0];

        $missionGameConditions = $this->findMissionGameConditionByMissionGame($nextMissionGame);
        
        if(empty($missionGameConditions)){
            return false;
        }

        foreach ($missionGameConditions as $missionGameCondition) {

            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::NONE) {
                continue;
            }

            // On passe au suivant si on a gagné
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::VICTORY) {
                if(!($winner || $prediction)){
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::DEFEAT) {
                if($winner || $prediction){
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::GREATER) {
                if (!$entry) {
                    return false;
                }
                if(!($entry->getPoints() > $missionGameCondition->getValue())){
                    return false;
                }
            }

            // On passe au suivant si on a perdu
            if ($missionGameCondition->getAttribute() == MissionGameConditionEntity::LESS) {
                if (!$entry) {
                    return false;
                }
                if(!($entry->getPoints() < $missionGameCondition->getValue())){
                    return false;
                }
            }
        }

        return $nextMissionGame->getGame();
    }

    /**
    * findMissionGameByMission : Permet de recuperer les missionsGame à partir d'une mission
    * @param Mission $mission
    *
    * @return Collection de MissionGame $missionGames
    */
    public function findMissionGameByMission($mission){
        return $this->getMissionGameMapper()->findBy(array('mission'=>$mission));
    }

    /**
    * findMissionGameByMission : Permet de recuperer les missionsGame à partir d'une mission
    * @param Mission $mission
    *
    * @return Collection de MissionGame $missionGames
    */
    public function findMissionGameByGame($game){
        return $this->getMissionGameMapper()->findOneBy(array('game'=>$game));
    }

    /**
    * findMissionGameConditionByMissionGame : Permet de recuperer les missionsGameCondition à partir d'une missionGame
    * @param MissionGame $missionGame
    *
    * @return Collection de MissionGameCondition $missionGameConditions
    */
    public function findMissionGameConditionByMissionGame($missionGame)
    {
        return $this->getMissionGameConditionMapper()->findBy(array('missionGame'=>$missionGame));
    } 

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $locator
     * @return Event
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

     /**
    * getMissionGameConditionMapper : retrieve missionGameCondition mapper instance
    *
    * @return Mapper/missionGameCondition $missionGameConditionMapper
    */
    public function getMissionGameConditionMapper()
    {
        if (null === $this->missionGameConditionMapper) {
            $this->missionGameConditionMapper = $this->getServiceManager()->get('playgroundgame_mission_game_condition_mapper');
        }

        return $this->missionGameConditionMapper;   
    }

    /**
    * getMissionGameMapper : retrieve missionGame mapper instance
    *
    * @return Mapper/MissionGameMapper $missionGameMapper
    */
    public function getMissionGameMapper()
    {
        if (null === $this->missionGameMapper) {
            $this->missionGameMapper = $this->getServiceManager()->get('playgroundgame_mission_game_mapper');
        }

        return $this->missionGameMapper;
    }

    /**
    * getGameMapper : retrieve game mapper instance
    *
    * @return Mapper/GameMapper $gameMapper
    */
    public function getGameMapper()
    {
        if (null === $this->gameMapper) {
            $this->gameMapper = $this->getServiceManager()->get('playgroundgame_game_mapper');
        }

        return $this->gameMapper;
    }
}
