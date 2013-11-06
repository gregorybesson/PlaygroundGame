<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_mission_game_condition")
 */
class MissionGameCondition
{


    public static $conditions = array('0' => 'noting',
                                     '1' => 'victory',
                                     '2' => 'defeat',
                                     '3' => 'greater than x points',
                                     '4' => 'less than x points');
    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /**
     * @ORM\ManyToOne(targetEntity="PlaygroundGame\Entity\MissionGame")
     * @ORM\JoinColumn(name="mission_game_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $missionGame;
    
     /**
     * @ORM\Column(name="attribute", type="string", nullable=true)
     */
    protected $attribute;

    /**
     * @ORM\Column(name="comparison", type="string", nullable=true)
     */
    protected $comparison;

    /**
     * @ORM\Column(name="value", type="string", nullable=true)
     */
    protected $value;

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
    public function __construct() {
    }

     /**
     * @param $id
     * @return Block|mixed
     */
    public function setMissionGame($missionGame)
    {
        $this->missionGame = $missionGame;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMissionGame()
    {
        return $this->missionGame;
    }

     /**
     * @param $id
     * @return Block|mixed
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

     /**
     * @param $id
     * @return Block|mixed
     */
    public function setComparison($comparison)
    {
        $this->comparison = $comparison;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComparison()
    {
        return $this->comparison;
    }

     /**
     * @param $id
     * @return Block|mixed
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
     * @return Block|mixed
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

     /**
     * @param $id
     * @return Block|mixed
     */
    public function setPosition($position)
    {
        $this->position = (int) $position;

        return $this;
    }

    /**
     * @return mixed
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
     * @return the $game
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * @param field_type $mission
     */
    public function setMission($mission)
    {
        $this->mission = $mission;

        return $this;
    }
  
    /**
     *
     * @return the $createdAt
     */
    public function getCreatedAt ()
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt ($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     *
     * @return the $updatedAt
     */
    public function getUpdatedAt ()
    {
        return $this->updatedAt;
    }

    /**
     *
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt ($updatedAt)
    {
        $this->updatedAt = $updatedAt;
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

    public function getInputFilter ()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();
            $this->inputFilter = $inputFilter;
        }
    
        return $this->inputFilter;
    }
}
