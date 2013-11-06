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
 * @ORM\Table(name="game_mission_game")
 */
class MissionGame
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
     * @ORM\ManyToOne(targetEntity="PlaygroundGame\Entity\Mission")
     * @ORM\JoinColumn(name="mission_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $mission;

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
    public function __construct() {
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
