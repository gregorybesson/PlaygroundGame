<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * An entry represent a game session.
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_entry")
 */
class Entry
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Game")
     **/
    protected $game;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active = 1;

    /**
     * An entry marked as bonus means that it's not taken into account
     * in the limit calculation. It's offered, for example, when you share the game to a friend.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $bonus = 0;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $points;

    /**
     * Is this entry a winning one ?
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $winner = 0;
    
    /**
     * Is there a termsOptin on this entry ?
     * @ORM\Column(name="terms_optin", type="boolean", nullable=true)
     */
    protected $termsOptin;
    
    /**
     * Is this entry eligible to draw ?
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $drawable = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    public function __construct()
    {

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
     *
     * @return the $id
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     *
     * @param field_type $id
     */
    public function setId ($id)
    {
        $this->id = $id;
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
     * @return the status
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param field_type $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return the bonus
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * @param field_type $bonus
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;
    }

    /**
     * @return the status
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param field_type $winner
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;
    }
    
    /**
     * @return the $drawable
     */
    public function getDrawable()
    {
        return $this->drawable;
    }

	/**
     * @param number $drawable
     */
    public function setDrawable($drawable)
    {
        $this->drawable = $drawable;
    }

	/**
     * @return the termsOptin
     */
    public function getTermsOptin()
    {
    	return $this->termsOptin;
    }
    
    /**
     * @param field_type $termsOptin
     */
    public function setTermsOptin($termsOptin)
    {
    	$this->termsOptin = $termsOptin;
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
