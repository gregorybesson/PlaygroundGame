<?php

namespace PlaygroundGame\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_treasurehunt_puzzle")
 */
class TreasureHuntPuzzle implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="TreasureHuntPuzzlePiece", mappedBy="puzzle", cascade={"persist", "remove"})
     **/
    protected $pieces;

    /**
     * @ORM\ManyToOne(targetEntity="TreasureHunt", inversedBy="puzzles")
	 * @ORM\JoinColumn(name="treasurehunt_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $treasurehunt;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position=0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $url;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $domain;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $hint;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $area;

    /**
     * This is the puzzle image in the case of a playground game
     * @ORM\Column(name="image", type="string", nullable=true)
     */
    protected $image = '';

    /**
     * In case of a "7 errors" type of puzzle, this is reference image the player compares to
     * @ORM\Column(name="reference_image", type="string", nullable=true)
     */
    protected $referenceImage = '';

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $timer = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $timer_duration = 0;

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
        $this->pieces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param unknown_type $pieces
     */
    public function setPieces($pieces)
    {
    	$this->pieces = $pieces;

    	return $this;
    }

    /**
     * Get pieces.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPieces()
    {
    	return $this->pieces;
    }

    /**
     * Get piece.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPiece($id)
    {
        return $this->pieces[$id];
    }

    /**
     * Add a puzzle to the hunt.
     *
     *
     * @return void
     */
    public function addPiece($puzzle)
    {
    	$this->pieces[] = $puzzle;
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
    }

	/**
	 * @return the $position
	 */
	public function getPosition()
	{
		return $this->position;
	}

	/**
	 * @param field_type $position
	 */
	public function setPosition($position)
	{
		$this->position = $position;

		return $this;
	}

	/**
	 * @return the $url
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * @param field_type $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;

		return $this;
	}

	/**
	 * @return the $domain
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * @param field_type $domain
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;

		return $this;
	}

	/**
	 * @return the $hint
	 */
	public function getHint()
	{
		return $this->hint;
	}

	/**
	 * @param field_type $hint
	 */
	public function setHint($hint)
	{
		$this->hint = $hint;

		return $this;
	}

	/**
	 * @return the $area
	 */
	public function getArea()
	{
		return $this->area;
	}

	/**
	 * @param field_type $area
	 */
	public function setArea($area)
	{
		$this->area = $area;

		return $this;
	}

	/**
     * @return the $image
     */
    public function getImage()
    {
        return $this->image;
    }

	/**
     * @param string $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return the $referenceImage
     */
    public function getReferenceImage()
    {
        return $this->referenceImage;
    }

	/**
     * @param string $referenceImage
     */
    public function setReferenceImage($referenceImage)
    {
        $this->referenceImage = $referenceImage;
    }

	/**
	 * @return the $timer
	 */
	public function getTimer()
	{
		return $this->timer;
	}

	/**
	 * @param number $timer
	 */
	public function setTimer($timer)
	{
		$this->timer = $timer;

		return $this;
	}

	/**
	 * @return the $timer_duration
	 */
	public function getTimer_duration()
	{
		return $this->timer_duration;
	}

	/**
	 * @param number $timer_duration
	 */
	public function setTimer_duration($timer_duration)
	{
		$this->timer_duration = $timer_duration;

		return $this;
	}

	/**
     * @PrePersist
     */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /**
     * @PreUpdate
     */
    public function updateChrono()
    {
        $this->updated_at = new \DateTime("now");
    }

    /**
     * @return the unknown_type
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param unknown_type $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getTreasurehunt()
    {
        return $this->treasurehunt;
    }

    /**
     * @param unknown_type $treasurehunt
     */
    public function setTreasurehunt($treasurehunt)
    {
        $this->treasurehunt = $treasurehunt;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param unknown_type $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param unknown_type $updated_at
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

        $this->title        = (isset($data['title'])) ? $data['title'] : null;
        $this->identifier   = (isset($data['identifier'])) ? $data['identifier'] : null;

    	if (isset($data['hint']) && $data['hint'] != null) {
    		$this->hint = $data['hint'];
    	}

    	if (isset($data['area']) && $data['area'] != null) {
    		$this->area = $data['area'];
    	}

    	if (isset($data['position']) && $data['position'] != null) {
    		$this->position = $data['position'];
    	}

    	if (isset($data['url']) && $data['url'] != null) {
    		$this->url = $data['url'];
    	}

    	if (isset($data['domain']) && $data['domain'] != null) {
    		$this->domain = $data['domain'];
    	}

    	if (isset($data['timer']) && $data['timer'] != null) {
    		$this->timer = $data['timer'];
    	}

    	if (isset($data['timer_duration']) && $data['timer_duration'] != null) {
    		$this->timer_duration = $data['timer_duration'];
    	}

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
                'name'       => 'id',
                'required'   => true,
                'filters'    => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'title',
                'required' => true,
                'filters' => array(
                    array(
                        'name' => 'StripTags'
                    ),
                    array(
                        'name' => 'StringTrim'
                    )
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 5,
                            'max' => 255
                        )
                    )
                )
            )));

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
}
