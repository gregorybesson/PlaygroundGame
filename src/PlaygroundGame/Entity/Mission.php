<?php
namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_mission")
 */
class Mission
{

    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\OneToMany(targetEntity="MissionGame", mappedBy="mission", cascade={"persist","remove"})
     */
    private $missionGames;

    /**
     * titre
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * image
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * description
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    protected $description;

    /**
     * hidden
     * @ORM\Column(name="hidden", type="boolean", nullable=false)
     */
    protected $hidden;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $active = 0;

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
        $this->missionGames = new ArrayCollection();
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
     * Setter for Id
     *
     * @param $id
     * @return Mission $mision
     */
    public function setId($id)
    {
        $this->id = (int) $id;

        return $this;
    }

    /**
     * Getter for id
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Getter for title
     *
     * @return string $title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Setter for title
     *
     * @param string $title Value to set
     * @return Mission $mission
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
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
     * Getter for image
     *
     * @return string $image
     */
    public function getImage()
    {
        return $this->image;
    }
    
    /**
     * Setter for image
     *
     * @param string $image Value to set
     * @return Mission $mission
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }
    
     /**
     * Getter for description
     *
     * @return string $description
     */
    public function getDescription()
    {
        return $this->description;
    }
    
    /**
     * Setter for description
     *
     * @param string $description Value to set
     * @return Mission $mission
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Getter for hidden
     *
     * @return int $hidden
     */
    public function getHidden()
    {
        return $this->hidden;
    }
    
    /**
     * Setter for hidden
     *
     * @param mixed $hidden Value to set
     * @return self
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     *
     * @return the unknown_type
     */
    public function getActive ()
    {
        return $this->active;
    }

    /**
     *
     * @param unknown_type $active
     */
    public function setActive ($active)
    {
        $this->active = $active;

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
        if (isset($data['title']) && $data['title'] != null) {
            $this->title = $data['title'];
        }

        if (isset($data['description']) && $data['description'] != null) {
            $this->description = $data['description'];
        }

        if (isset($data['hidden']) && $data['hidden'] != null) {
            $this->hidden = $data['hidden'];
        }

        if (isset($data['image']) && $data['image'] != null) {
            $this->image = $data['image'];
        }
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
