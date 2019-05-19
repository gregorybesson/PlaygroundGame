<?php
namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

use PlaygroundGame\Entity\Game;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_memory")
 */

class Memory extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'memory';

    /**
     * the cards associated with the game
     * @ORM\OneToMany(targetEntity="MemoryCard", mappedBy="game")
     */
    protected $cards;

    /**
     * @ORM\Column(name="back_image", type="string", length=255, nullable=true)
     */
    protected $backImage;

    /**
     * @ORM\Column(name="victory_conditions", type="integer", nullable=false)
     */
    protected $victoryConditions = 0;

    public function __construct()
    {
        parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->cards = new ArrayCollection();
    }

    /**
     * Gets the the cards associated with the game.
     *
     * @return mixed
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Sets the the cards associated with the game.
     *
     * @param mixed $cards the cards
     *
     * @return self
     */
    protected function setCards($cards)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Add a card to the trading card.
     *
     *
     * @return void
     */
    public function addCard($card)
    {
        $this->cards[] = $card;
    }

    /**
     *
     * @return the $backImage
     */
    public function getBackImage()
    {
        return $this->backImage;
    }

    /**
     *
     * @param field_type $backImage
     */
    public function setBackImage($backImage)
    {
        $this->backImage = $backImage;
        
        return $this;
    }

    public function getVictoryConditions()
    {
        return $this->victoryConditions;
    }

    /**
     */
    public function setVictoryConditions($victoryConditions)
    {
        $this->victoryConditions = $victoryConditions;

        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = parent::getArrayCopy();
        array_merge($obj_vars, get_object_vars($this));

        return $obj_vars;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $inputFilter = parent::getInputFilter();

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
