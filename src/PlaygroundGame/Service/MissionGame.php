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
    * @var leaderboardType
    */
    protected $missionMapper;
    protected $missionGameMapper;
    protected $missionGameConditionMapper;
    protected $gameMapper;

    protected $options;

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

    public function clear($mission)
    {
        $missionGames = $this->getMissionGameMapper()->findBy(array('mission'=>$mission));
        foreach ($missionGames as $missionGames) {
            $this->getMissionGameMapper()->remove($missionGames); 
        }
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

    public function getMissionGameConditionMapper()
    {
        if (null === $this->missionGameConditionMapper) {
            $this->missionGameConditionMapper = $this->getServiceManager()->get('playgroundgame_mission_game_condition_mapper');
        }

        return $this->missionGameConditionMapper;   
    }

     /**
     * getLeaderboardTypeMapper : retrieve LeaderBoardType mapper instance
     *
     * @return Mapper/LeaderBoardType $leaderboardType
     */
    public function getMissionGameMapper()
    {
        if (null === $this->missionGameMapper) {
            $this->missionGameMapper = $this->getServiceManager()->get('playgroundgame_mission_game_mapper');
        }

        return $this->missionGameMapper;
    }


    public function getGameMapper()
    {
        if (null === $this->gameMapper) {
            $this->gameMapper = $this->getServiceManager()->get('playgroundgame_game_mapper');
        }

        return $this->gameMapper;
    }
}
