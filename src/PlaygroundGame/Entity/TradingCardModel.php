<?php

namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_tradingcard_model")
 * @Gedmo\TranslationEntity(class="PlaygroundGame\Entity\GameTranslation")
 */
class TradingCardModel implements InputFilterAwareInterface, \JsonSerializable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="TradingCard", inversedBy="models")
     * @ORM\JoinColumn(name="game_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $game;

    /**
     * @ORM\OneToMany(targetEntity="TradingCardCard", mappedBy="model", cascade={"persist","remove"})
     */
    private $cards;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * The type of the card : collector, standard, ...
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $type;

    /**
     * a card can be part of a set of cards
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $family;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points = 0;

    /**
     * @ORM\Column(type="float", nullable=false)
     */
    protected $distribution = 1;

    /**
     * datetime when this model will be available
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $availability;

    /**
     * @ORM\Column(name="json_data", type="text", nullable=true)
     */
    protected $jsonData;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at",type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    /**
     * @PrePersist
     */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * @PreUpdate
     */
    public function updateChrono()
    {
        $this->updatedAt = new \DateTime("now");
    }

    /**
     * Gets the value of id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the value of id.
     *
     * @param mixed $id the id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets the value of title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the value of title.
     *
     * @param mixed $title the title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the value of description.
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the value of description.
     *
     * @param mixed $description the description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Gets the value of image.
     *
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Sets the value of image.
     *
     * @param mixed $image the image
     *
     * @return self
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Gets the value of type.
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the value of type.
     *
     * @param mixed $type the type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the value of family.
     *
     * @return mixed
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * Sets the value of family.
     *
     * @param mixed $family the family
     *
     * @return self
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * Gets the value of points.
     *
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Sets the value of points.
     *
     * @param mixed $points the points
     *
     * @return self
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * Gets the value of distribution.
     *
     * @return mixed
     */
    public function getDistribution()
    {
        return $this->distribution;
    }

    /**
     * Sets the value of distribution.
     *
     * @param mixed $distribution the distribution
     *
     * @return self
     */
    public function setDistribution($distribution)
    {
        $this->distribution = $distribution;

        return $this;
    }

    /**
     * Gets the value of availability.
     *
     * @return mixed
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Sets the value of availability.
     *
     * @param mixed $availability the availability
     *
     * @return self
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * Gets the value of game.
     *
     * @return mixed
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Sets the value of game.
     *
     * @param mixed $game the game
     *
     * @return self
     */
    public function setGame($game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * Gets the value of jsonData.
     *
     * @return mixed
     */
    public function getJsonData()
    {
        return $this->jsonData;
    }

    /**
     * Sets the value of jsonData.
     *
     * @param mixed $jsonData the json data
     *
     * @return self
     */
    public function setJsonData($jsonData)
    {
        $this->jsonData = $jsonData;

        return $this;
    }

    /**
     * Gets the value of cards.
     *
     * @return mixed
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Sets the value of cards.
     *
     * @param mixed $cards the cards
     *
     * @return self
     */
    private function setCards($cards)
    {
        $this->cards = $cards;

        return $this;
    }

    public function addCards(ArrayCollection $cards)
    {
        foreach ($cards as $card) {
            $card->setModel($this);
            $this->cards->add($card);
        }
    }

    public function removeCards(ArrayCollection $cards)
    {
        foreach ($cards as $card) {
            $card->setModel(null);
            $this->cards->removeElement($card);
        }
    }

    /**
     * Add an card to the Model.
     *
     * @param TradingCardModel $card
     *
     * @return void
     */
    public function addCard($card)
    {
        $this->cards[] = $card;
    }

    /**
     * Gets the value of createdAt.
     *
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the value of createdAt.
     *
     * @param mixed $createdAt the created at
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Gets the value of updatedAt.
     *
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Sets the value of updatedAt.
     *
     * @param mixed $updatedAt the updated at
     *
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        $obj_vars = get_object_vars($this);

        return $obj_vars;
    }

    /**
     * Convert the object to json.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $jsonArray = $this->getArrayCopy();
        unset($jsonArray['inputFilter']);
        unset($jsonArray['__initializer__']);
        unset($jsonArray['__cloner__']);
        unset($jsonArray['__isInitialized__']);
        unset($jsonArray['game']);
        unset($jsonArray['cards']);
        unset($jsonArray['distribution']);
        unset($jsonArray['createdAt']);
        unset($jsonArray['updatedAt']);
        
        return $jsonArray;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        if (isset($data['title']) && $data['title'] !== null) {
            $this->title = $data['title'];
        }

        if (isset($data['description']) && $data['description'] !== null) {
            $this->description = $data['description'];
        }

        if (isset($data['jsonData']) && $data['jsonData'] !== null) {
            $this->jsonData = $data['jsonData'];
        }

        if (isset($data['distribution']) && $data['distribution'] !== null) {
            $this->distribution = $data['distribution'];
        }

        if (isset($data['type']) && $data['type'] !== null) {
            $this->type = $data['type'];
        }

        if (isset($data['family']) && $data['family'] !== null) {
            $this->family = $data['family'];
        }

        $this->points = (!empty($data['points'])) ?
            $this->points = $data['points']:
            0;

        if (!empty($data['image'])) {
            $this->image = $data['image'];
        }

        $this->availability  = (!empty($data['availability'])) ?
            \DateTime::createFromFormat('d/m/Y H:i:s', $data['availability']) :
            null;
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'availability',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
