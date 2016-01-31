<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;

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
        foreach ($missionGames as $missionGame) {
            $missionGame->setMission($this);
            $this->missionGames->add($missionGame);
        }
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
     * Get the playables game if any
     *
     * @return array
     */
    public function getPlayableGames($entry = null)
    {
        $sortedPlayableGames = array();
        foreach ($this->missionGames as $missionGame) {
            $g = $missionGame->getGame();
            if ($g->isStarted() && $g->isOnline()) {
                if (!$missionGame->getConditions() || $missionGame->fulfillConditions($entry)) {
                    $sortedPlayableGames[$missionGame->getPosition()] = $missionGame;
                }
            }
        }

        return $sortedPlayableGames;
    }

    /**
     * Get the next playable game if any
     *
     * @return \PlaygroundGame\Entity\Game
     */
    public function getNextPlayableGame($entry = null)
    {
        $sortedPlayableGames = $this->getPlayableGames($entry);

        return (count($sortedPlayableGames)>=1)?current($sortedPlayableGames)->getGame():null;
    }

    /**
     * is this game playable ?
     *
     * @return boolean
     */
    public function isPlayable($subGame, $entry = null)
    {
        if (!$subGame) {
            return false;
        }
        
        $sortedPlayableGames = $this->getPlayableGames($entry);
        foreach ($sortedPlayableGames as $pgame) {
            if ($subGame->getIdentifier() === $pgame->getGame()->getIdentifier()) {
                return true;
            }
        }

        return false;
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
