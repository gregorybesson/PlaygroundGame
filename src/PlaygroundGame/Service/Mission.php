<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundReward\Options\ModuleOptions;
use PlaygroundGame\Entity\Mission as MissionEntity;
use PlaygroundGame\Entity\MissionGame as MissionGameEntity;

class Mission extends EventProvider implements ServiceManagerAwareInterface
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
    * @var missionGameService
    */
    protected $missionGameService;
    /**
    * @var gameMapper
    */
    protected $gameMapper;
    /**
    * @var options
    */
    protected $options;

    /**
    * create : ajout de Mission
    * @param array $data 
    * @param string $formClass
    *
    * @return Mission $mission
    */
    public function create(array $data, $formClass)
    {

        $mission = new MissionEntity();

        $form = $this->getServiceManager()->get($formClass);

        $form->bind($mission);
       
        $form->setData($data);

        if (!$form->isValid()) {
            
            return false;
        }
        
        $mission = $this->getMissionMapper()->insert($mission);

        $this->uploadImage($mission, $data);
        $mission = $this->getMissionMapper()->update($mission);

        return $mission;
    }

    /**
    * edit : mise ajour de mission
    * @param array $data 
    * @param Mission $mission
    * @param string $formClass
    *
    * @return Mission  $mission
    */
    public function edit($mission, $data)
    {

        $this->uploadImage($mission, $data);
        $mission = $this->getMissionMapper()->update($mission);

        return $mission;
    }

    /**
    * uploadImage : upload de l'image de la mission
    * @param Mission $mission
    * @param array $data 
    *
    * @return Mission $mission
    */
    public function uploadImage($mission, $data)
    {
         if (!empty($data['uploadImage']['tmp_name'])) {
            $path = $this->getOptions()->getMediaPathMission().'/';
            if (!is_dir($path)) {
                mkdir($path,0777, true);
            }
            $media_url = $this->getOptions()->getMediaUrlMission() . '/';
            move_uploaded_file($data['uploadImage']['tmp_name'], $path . $mission->getId() . "-" . $data['uploadImage']['name']);
            $mission->setImage($media_url . $mission->getId() . "-" . $data['uploadImage']['name']);
        }

        return $mission;
    }

    public function getActiveMissions()
    {
        
        $missionsArray = array();

        $missions = $this->getMissionMapper()->findBy(array('active'=>true));
        foreach ($missions as $mission) {
            $games = $this->getGames($mission);
            if (count($games) == 0) {
                continue;
            }
            $missionsArray[] = array('mission' => $mission,
                                     'games' => $games);
        }

        return $missionsArray;
    }

    public function getGames($mission)
    {
        $games = array();
        $missionGames = $this->getMissionGameService()->findMissionGameByMission($mission);
        foreach ($missionGames as $missionGame) {
            $games[] = $missionGame->getGame();
        }

        return $games;
    }
    
    /** 
    * findById : recupere l'entite en fonction de son id
    * @param int $id id du mission
    *
    * @return PlaygroundGame\Entity\Mission $mission
    */
    public function findById($id)
    {
        return $this->getMissionMapper()->findById($id);
    }

    /**
    * remove : supprimer une entite mission
    * @return PlaygroundGame\Entity\Mission  $entity mission
    *
    */
    public function remove($entity)
    {
        return $this->getMissionMapper()->remove($entity);
    }

    /**
    * update : met a jour  une entite mission
    * @return PlaygroundGame\Entity\Mission  $entity mission
    *
    */
    public function update($entity)
    {
        return $this->getMissionMapper()->update($entity);
    }

      /**
     * Retrieve options instance
     *
     * @return Options $options
     */
    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundgame_module_options'));
        }

        return $this->options;
    }

    /**
     * Set options instance
     *
     * @param  Option $options
     * @return Mission
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
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
     * @return Mission
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }


     /**
     * getMissionMapper : retrieve Mission mapper instance
     *
     * @return Mapper/Mission $missionMapper
     */
    public function getMissionMapper()
    {
        if (null === $this->missionMapper) {
            $this->missionMapper = $this->getServiceManager()->get('playgroundgame_mission_mapper');
        }

        return $this->missionMapper;
    }

    /**
     * getMissionGameService : retrieve Mission game service instance
     *
     * @return Service/MissionGame $missionGameService
     */
    public function getMissionGameService()
    {
        if (null === $this->missionGameService) {
            $this->missionGameService = $this->getServiceManager()->get('playgroundgame_mission_game_service');
        }

        return $this->missionGameService;
    }

}