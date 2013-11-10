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
 * @ORM\Table(name="game_prize_category")
 */
class PrizeCategory
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $identifier;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active = 1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $picto;

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
     * @return the unknown_type
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param unknown_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     *
     * @return the $identifier
     */
    public function getIdentifier ()
    {
        return $this->identifier;
    }

    /**
     *
     * @param field_type $identifier
     */
    public function setIdentifier ($identifier)
    {
        $this->identifier = $identifier;
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
     * @return the picto
     */
    public function getPicto()
    {
        return $this->picto;
    }

    /**
     * @param string $picto
     */
    public function setPicto($picto)
    {
        $this->picto = $picto;
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
        if (isset($data['title']) && $data['title'] != null) {
            $this->title = $data['title'];
        }

        if (isset($data['identifier']) && $data['identifier'] != null) {
            $this->identifier = $data['identifier'];
        }

        if (isset($data['active']) && $data['active'] != null) {
            $this->active = $data['active'];
        }

        if (isset($data['picto']) && $data['picto'] != null) {
            $this->picto = $data['picto'];
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
