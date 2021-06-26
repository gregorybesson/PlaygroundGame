<?php
namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

use PlaygroundGame\Entity\Game;

use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_tradingcard")
 */

class TradingCard extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'tradingcard';

    /**
     * the card models associated with the game
     * @ORM\OneToMany(targetEntity="TradingCardModel", mappedBy="game")
     */
    protected $models;

    /**
     * The number of cards in a booster
     * @ORM\Column(name="booster_card_number", type="integer", nullable=true)
     */
    protected $boosterCardNumber;

    /**
     * The number of boosters to deliver to user for each entry
     * @ORM\Column(name="booster_draw_quantity", type="integer", nullable=true)
     */
    protected $boosterDrawQuantity;

    public function __construct()
    {
        parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->models = new ArrayCollection();
    }

    /**
     * Gets the the card models associated with the game.
     *
     * @return mixed
     */
    public function getModels()
    {
        return $this->models;
    }

    /**
     * Sets the the card models associated with the game.
     *
     * @param mixed $models the models
     *
     * @return self
     */
    protected function setModels($models)
    {
        $this->models = $models;

        return $this;
    }

    /**
     * Add a model to the trading card.
     *
     *
     * @return void
     */
    public function addModel($model)
    {
        $this->models[] = $model;
    }

    /**
     * Gets the The number of cards in a booster.
     *
     * @return mixed
     */
    public function getBoosterCardNumber()
    {
        return $this->boosterCardNumber;
    }

    /**
     * Sets the The number of cards in a booster.
     *
     * @param mixed $boosterCardNumber the booster card number
     *
     * @return self
     */
    public function setBoosterCardNumber($boosterCardNumber)
    {
        $this->boosterCardNumber = $boosterCardNumber;

        return $this;
    }

    /**
     * Gets the The number of boosters to deliver to user.
     *
     * @return mixed
     */
    public function getBoosterDrawQuantity()
    {
        return $this->boosterDrawQuantity;
    }

    /**
     * Sets the The number of boosters to deliver to user.
     *
     * @param mixed $boosterDrawQuantity the booster draw quantity
     *
     * @return self
     */
    public function setBoosterDrawQuantity($boosterDrawQuantity)
    {
        $this->boosterDrawQuantity = $boosterDrawQuantity;

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
