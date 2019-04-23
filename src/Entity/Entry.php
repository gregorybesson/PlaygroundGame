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
class Entry implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Game", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $game;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     **/
    protected $user;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active = 1;

    /**
     * Has this entry been paid
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $paid = false;

    /**
     * The amount paid for this entry
     * @ORM\Column(name="paid_amount", type="integer", nullable=true)
     */
    protected $paidAmount = 0;

    /**
     * An entry marked as bonus means that it's not taken into account
     * in the limit calculation. It's offered, for example, when you share the game to a friend.
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $bonus = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $ip;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $geoloc;
    
    /**
     * @ORM\Column(name="anonymous_id", type="string", length=255, nullable=true)
     */
    protected $anonymousId;
    
    /**
     * This column hosts an id chosen by the admin in case of a game with "anonymous" participation :
     * This is a value chosen from the playerData (generally "email").
     *
     * @ORM\Column(name="anonymous_identifier", type="string", length=255, nullable=true)
     */
    protected $anonymousIdentifier;
    
    /**
     * @ORM\Column(name="player_data", type="text", nullable=true)
     */
    protected $playerData;

    /**
     * @ORM\Column(name="social_shares", type="text", nullable=true)
     */
    protected $socialShares;
    
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
     * The step in the play (in a quiz for example : the nth question)
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $step = 0;

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
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @param field_type $id
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
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
        
        return $this;
    }

    /**
     * @return integer $points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param integer $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
        
        return $this;
    }

    /**
     * @return the $ip
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param field_type $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        
        return $this;
    }

    /**
     * @return the $geoloc
     */
    public function getGeoloc()
    {
        return $this->geoloc;
    }

    /**
     * @param field_type $geoloc
     */
    public function setGeoloc($geoloc)
    {
        $this->geoloc = $geoloc;
        
        return $this;
    }

    /**
     * @return the $anonymousId
     */
    public function getAnonymousId()
    {
        return $this->anonymousId;
    }

    /**
     * @param field_type $anonymousId
     */
    public function setAnonymousId($anonymousId)
    {
        $this->anonymousId = $anonymousId;
        
        return $this;
    }

    /**
     * @return the $anonymousIdentifier
     */
    public function getAnonymousIdentifier()
    {
        return $this->anonymousIdentifier;
    }

    /**
     * @param field_type $anonymousIdentifier
     */
    public function setAnonymousIdentifier($anonymousIdentifier)
    {
        $this->anonymousIdentifier = $anonymousIdentifier;
    }

    /**
     * @return the $playerData
     */
    public function getPlayerData()
    {
        return $this->playerData;
    }

    /**
     * @param field_type $playerData
     */
    public function setPlayerData($playerData)
    {
        $this->playerData = $playerData;
        
        return $this;
    }

    /**
     * @return the $socialShares
     */
    public function getSocialShares()
    {
        return $this->socialShares;
    }

    /**
     * @param field_type $socialShares
     */
    public function setSocialShares($socialShares)
    {
        $this->socialShares = $socialShares;
    }

    /**
     * @return boolean active
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
        
        return $this;
    }

    /**
     * @return boolean paid
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * @param boolean $paid
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;
        
        return $this;
    }

    /**
     * @return integer paidAmount
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * @param integer $paidAmount
     */
    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
        
        return $this;
    }

    /**
     * @return integer $step
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * @param number $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @return integer bonus
     */
    public function getBonus()
    {
        return $this->bonus;
    }

    /**
     * @param integer $bonus
     */
    public function setBonus($bonus)
    {
        $this->bonus = $bonus;
        
        return $this;
    }

    /**
     * @return integer status
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param integer $winner
     */
    public function setWinner($winner)
    {
        $this->winner = $winner;
        
        return $this;
    }
    
    /**
     * @return integer $drawable
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
        
        return $this;
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
        
        return $this;
    }

    /**
     * @return \DateTime $created_at
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
        
        return $this;
    }

    /**
     * @return \DateTime $updated_at
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
        
        return $this;
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
    * Convert the object to json.
    *
    * @return array
    */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
    }
}
