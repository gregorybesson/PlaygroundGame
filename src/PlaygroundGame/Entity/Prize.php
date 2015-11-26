<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_prize")
 */
class Prize implements \JsonSerializable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="prizes")
     *
     **/
    protected $game;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\ManyToOne(targetEntity="PrizeCategory")
     * @ORM\JoinColumn(name="prize_category_id", referencedColumnName="id")
     **/
    protected $prizeCategory;

    /**
     * @ORM\Column(name="prize_content",type="text", nullable=true)
     */
    protected $prizeContent;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $qty = 0;

    /**
     * @ORM\Column(name="unit_price", type="float", nullable=false)
     */
    protected $unitPrice = 1;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    protected $currency;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $picture;

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

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param unknown_type $game
     */
    public function setGame($game)
    {
        $this->game = $game;

        return $this;
    }

    /**
     * @return the $title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param field_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return the $identifier
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param field_type $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return the $prizeCategory
     */
    public function getPrizeCategory()
    {
        return $this->prizeCategory;
    }

    /**
     * @param unknown $prizeCategory
     */
    public function setPrizeCategory($prizeCategory)
    {
        $this->prizeCategory = $prizeCategory;

        return $this;
    }

    /**
     * @return the $prizeContent
     */
    public function getPrizeContent()
    {
        return $this->prizeContent;
    }

    /**
     */
    public function setPrizeContent($prizeContent)
    {
        $this->prizeContent = $prizeContent;

        return $this;
    }

    /**
     * @return integer $qty
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param number $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * @return integer $unitPrice
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @param number $unitPrice
     */
    public function setUnitPrice($unitPrice)
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    /**
     * @return the $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param field_type $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return \DateTime $created_at
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return the $picture
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param field_type $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

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
        return $this->getArrayCopy();
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        if (isset($data['prizeContent']) && $data['prizeContent'] !== null) {
            $this->prizeContent = $data['prizeContent'];
        }

        if (isset($data['title']) && $data['title'] !== null) {
            $this->title = $data['title'];
        }

        if (isset($data['qty']) && $data['qty'] !== null) {
            $this->qty = $data['qty'];
        }

        if (isset($data['identifier']) && $data['identifier'] !== null) {
            $this->identifier = $data['identifier'];
        }

        if (isset($data['unitPrice']) && $data['unitPrice'] !== null) {
            $this->unitPrice = $data['unitPrice'];
        }

        if (isset($data['currency']) && $data['currency'] !== null) {
            $this->currency = $data['currency'];
        }

        if (isset($data['picture']) && $data['picture'] !== null) {
            $this->picture = $data['picture'];
        }
    }

    /**
     * @return InputFilter $inputFilter
     */
    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name' => 'identifier',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    ),
                    array(
                        'name' => 'PlaygroundCore\Filter\Slugify'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 3,
                            'max' => 255
                        )
                    )
                )
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
}
