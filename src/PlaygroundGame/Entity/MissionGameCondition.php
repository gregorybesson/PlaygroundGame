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

    const NONE    = 0;
    const VICTORY = 1;
    const DEFEAT  = 2;
    const GREATER = 3;
    const LESS    = 4;
    /**
    * var $conditions
    * Tableau des types de conditions du jeu précendant pour passer au suivant.
    */
    public static $conditions = array(self::NONE     => 'none', // On passe directement au jeu suivant
                                      self::VICTORY  => 'victory', // Il faut une victoire pour passer au suivant
                                      self::DEFEAT   => 'defeat', // Il  faut une defaite pour passer au suivant
                                      self::GREATER  => 'greater than x points', // Il faut un nombre de points supérieur à x points pour passer au suivant 
                                      self::LESS     => 'less than x points'
                                ); // Il faut un nombre de points inférieur à x points pour passer au suivant
    protected $inputFilter;
    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="MissionGame", inversedBy="conditions")
     * 
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
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * @param field_type $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
