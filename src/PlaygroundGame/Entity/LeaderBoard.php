<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="leader_board")
 */
class LeaderBoard
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Game")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $game;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $points=0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    public function __construct($user, $game)
    {
        $this->setGame($game);
        $this->setUser($user);
    }

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
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
    }

    /**
     * @return the $user
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param field_type $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return the $points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param field_type $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
    }

    /**
     * @return the $created_at
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
    }

    /**
     * @return the $updated_at
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
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
        /*$this->title = $data['title'];
        $this->identifier = $data['identifier'];
        $this->welcome_block = $data['welcome_block'];*/
    }
}
