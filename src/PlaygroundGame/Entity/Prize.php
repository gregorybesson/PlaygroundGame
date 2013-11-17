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
class Prize {

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
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $content;

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
	 * @ORM\Column(type="datetime")
	 */
	protected $created_at;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $updated_at;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	protected $picture;

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
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
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
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param field_type $Title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @return the $identifier
	 */
	public function getIdentifier() {
		return $this->identifier;
	}

	/**
	 * @param field_type $identifier
	 */
	public function setIdentifier($identifier) {
		$this->identifier = $identifier;
	}

	/**
	 * @return the $prizeCategory
	 */
	public function getPrizeCategory() {
		return $this->prizeCategory;
	}

	/**
	 * @param unknown $prizeCategory
	 */
	public function setPrizeCategory($prizeCategory) {
		$this->prizeCategory = $prizeCategory;
	}

	/**
	 * @return the $content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param field_type $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @return the $qty
	 */
	public function getQty() {
		return $this->qty;
	}

	/**
	 * @param number $qty
	 */
	public function setQty($qty) {
		$this->qty = $qty;
	}

	/**
	 * @return the $unitPrice
	 */
	public function getUnitPrice() {
		return $this->unitPrice;
	}

	/**
	 * @param number $unitPrice
	 */
	public function setUnitPrice($unitPrice) {
		$this->unitPrice = $unitPrice;
	}

	/**
	 * @return the $currency
	 */
	public function getCurrency() {
		return $this->currency;
	}

	/**
	 * @param field_type $currency
	 */
	public function setCurrency($currency) {
		$this->currency = $currency;
	}

	/**
	 * @return the $created_at
	 */
	public function getCreated_at() {
		return $this->created_at;
	}

	/**
	 * @param \DateTime $created_at
	 */
	public function setCreated_at($created_at) {
		$this->created_at = $created_at;
	}

	/**
	 * @return the $updated_at
	 */
	public function getUpdated_at() {
		return $this->updated_at;
	}

	/**
	 * @param \DateTime $updated_at
	 */
	public function setUpdated_at($updated_at) {
		$this->updated_at = $updated_at;
	}

	/**
	 * @return the $picture
	 */
	public function getPicture() {
		return $this->picture;
	}

	/**
	 * @param field_type $picture
	 */
	public function setPicture($picture) {
		$this->picture = $picture;
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
	 * Populate from an array.
	 *
	 * @param array $data
	 */
	public function populate($data = array())
	{
		if (isset($data['content']) && $data['content'] != null) {
			$this->content = $data['content'];
		}
	
		if (isset($data['title']) && $data['title'] != null) {
			$this->title = $data['title'];
		}
	
		if (isset($data['qty']) && $data['qty'] != null) {
			$this->qty = $data['qty'];
		}
	
		if (isset($data['identifier']) && $data['identifier'] != null) {
			$this->identifier = $data['identifier'];
		}
	
		if (isset($data['unitPrice']) && $data['unitPrice'] != null) {
			$this->unitPrice = $data['unitPrice'];
		}
	
		if (isset($data['currency']) && $data['currency'] != null) {
			$this->currency = $data['currency'];
		}

		if (isset($data['picture']) && $data['picture'] != null) {
			$this->picture = $data['picture'];
		}
	}

	/**
	 * @return the $inputFilter
	 */
	public function getInputFilter() {
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
	 * @param field_type $inputFilter
	 */
	public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
}