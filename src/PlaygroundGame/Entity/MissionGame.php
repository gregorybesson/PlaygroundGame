<?php
namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\Factory as InputFactory;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_mission_game")
 */
class MissionGame implements InputFilterAwareInterface
{

    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\ManyToOne(targetEntity="PlaygroundGame\Entity\Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $game;

    /**
     * @ORM\ManyToOne(targetEntity="Mission", inversedBy="missionGames")
     * @ORM\JoinColumn(name="mission_id", referencedColumnName="id", onDelete="CASCADE")
     *
     **/
    protected $mission;
    
    /**
     * @ORM\OneToMany(targetEntity="MissionGameCondition", mappedBy="missionGame", cascade={"persist","remove"})
     */
    protected $conditions;

    /**
     * position
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    protected $position;

    /**
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->conditions = new ArrayCollection();
    }

    public function __clone()
    {
        $this->conditions = new ArrayCollection();
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * @param $id
     * @return MissionGame
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

     /**
     * @param $id
     * @return MissionGame
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return the $game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param field_type $game
     */
    public function setGame($game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return \PlaygroundGame\Service\Mission $game
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * @param \PlaygroundGame\Service\Mission $mission
     */
    public function setMission($mission)
    {
        $this->mission = $mission;

        return $this;
    }
  
    /**
     * @return the $conditions
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param field_type $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
        
        return $this;
    }
    
    public function addConditions(ArrayCollection $conditions)
    {
        foreach ($conditions as $condition) {
            $condition->setMissionGame($this);
            $this->conditions->add($condition);
        }
    }
    
    public function removeConditions(ArrayCollection $conditions)
    {
        foreach ($conditions as $condition) {
            $this->conditions->removeElement($condition);
        }
    }
    
    /**
     * Add a condition to the mission game.
     *
     * @param MisionGameCondition $condition
     *
     * @return void
     */
    public function addCondition($condition)
    {
        $this->conditions[] = $condition;
    }

    /**
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        
        return $this;
    }

    /**
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        
        return $this;
    }

    /**
     * Are the conditions linked to this game fulfilled ?
     *
     * @return boolean
     */
    public function fulfillConditions($entry = null)
    {
        foreach ($this->getConditions() as $condition) {
            if ($condition->getAttribute() == MissionGameCondition::NONE) {
                continue;
            }
    
            // On passe au suivant si on a gagné
            if ($condition->getAttribute() == MissionGameCondition::VICTORY) {
                if (!$entry || !$entry->getWinner()) {
                    return false;
                }
            }
    
            // On passe au suivant si on a perdu
            if ($condition->getAttribute() == MissionGameCondition::DEFEAT) {
                if (!$entry || $entry->getWinner()) {
                    return false;
                }
            }
    
            // On passe au suivant si on a plus de n points
            if ($condition->getAttribute() == MissionGameCondition::GREATER) {
                if (!$entry || !($entry->getPoints() >= $condition->getValue())) {
                    return false;
                }
            }
    
            // On passe au suivant si on a moins de n points
            if ($condition->getAttribute() == MissionGameCondition::LESS) {
                if (!$entry || !($entry->getPoints() < $condition->getValue())) {
                    return false;
                }
            }
        }

        return true;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'game',
                'required' => false,
            )));
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
