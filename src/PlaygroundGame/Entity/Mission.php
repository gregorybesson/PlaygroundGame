<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_mission")
 */
class Mission extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'mission';
    
    /**
     * @ORM\OneToMany(targetEntity="MissionGame", mappedBy="mission", cascade={"persist","remove"}, orphanRemoval=true)
     */
    protected $missionGames;

    public function __construct()
    {
        parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->missionGames = new ArrayCollection();
    }

     /**
     * @return the $missionGames
     */
    public function getMissionGames()
    {
        return $this->missionGames;
    }

    /**
     * @param \PlaygroundGame\Entity\ArrayCollection $missionGames
     */
    public function setMissionGames($missionGames)
    {
        $this->missionGames = $missionGames;
        
        return $this;
    }
    
    public function addMissionGames(ArrayCollection $missionGames)
    {
        //echo 'addMissionGames<br/>';
        foreach ($missionGames as $missionGame) {
            //echo 'uu ' . $missionGame->getId();

            $missionGame->setMission($this);
            $this->missionGames->add($missionGame);
        }
        //die('----fin----');
    }
    
    public function removeMissionGames(ArrayCollection $missionGames)
    {
        foreach ($missionGames as $missionGame) {
            $missionGame->setMission(null);
            $this->missionGames->removeElement($missionGame);
        }
    }
    
    /**
     * Add a game to the mission.
     *
     * @param MissionGame $missionGame
     *
     * @return void
     */
    public function addMissionGame($missionGame)
    {
        $this->missionGames[] = $missionGame;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {

    }

    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter = parent::getInputFilter();
            
            // This definition is mandatory for the hydration to work in a form !!!!
            $inputFilter->add($factory->createInput(array(
                'name' => 'missionGames',
                'required' => false,
            )));
            

            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
