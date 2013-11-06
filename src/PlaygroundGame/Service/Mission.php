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
    * @var leaderboardType
    */
    protected $missionMapper;
    protected $missionGameMapper;
    protected $gameMapper;

    protected $options;

    /**
    * create : ajout de leaderBoardType
    * @param array $data 
    * @param string $formClass
    *
    * @return LeaderBoardType $leaderboardType
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

        return $leaderboardType;
    }

    /**
    * edit : mise ajour de leaderBoardType
    * @param array $data 
    * @param LeaderBoardType $leaderboardType
    * @param string $formClass
    *
    * @return LeaderBoardType  $leaderboardType
    */
    public function edit(array $data, $mission, $formClass)
    {
        $form  = $this->getServiceManager()->get($formClass);

        $form->bind($mission);

        $form->setData($data);
        if (!$form->isValid()) {
            return false;
        }
        $this->uploadImage($mission, $data);
        $mission = $this->getMissionMapper()->update($mission);

        return $mission;
    }

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


    /* findById : recupere l'entite en fonction de son id
    * @param int $id id du leaderboardType
    *
    * @return PlaygroundFlow\Entity\leaderboardType $leaderboardType
    */
    public function findById($id)
    {
        return $this->getMissionMapper()->findById($id);
    }

    /**
    * remove : supprimer une entite leaderboardType
    * @return PlaygroundFlow\Entity\leaderboardType $entity leaderboardType
    *
    */
    public function remove($entity)
    {
        return $this->getMissionMapper()->remove($entity);
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
     * Set service manager instance
     *
     * @param  ServiceManager $locator
     * @return Event
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
     * @return Event
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }


     /**
     * getLeaderboardTypeMapper : retrieve LeaderBoardType mapper instance
     *
     * @return Mapper/LeaderBoardType $leaderboardType
     */
    public function getMissionMapper()
    {
        if (null === $this->missionMapper) {
            $this->missionMapper = $this->getServiceManager()->get('playgroundgame_mission_mapper');
        }

        return $this->missionMapper;
    }
}
