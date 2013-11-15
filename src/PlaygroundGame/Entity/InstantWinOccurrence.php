<?php

namespace PlaygroundGame\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_instantwin_occurrence")
 */
class InstantWinOccurrence implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="InstantWin", inversedBy="occurrences", cascade={"persist","remove"})
     * @ORM\JoinColumn(name="instantwin_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $instantwin;

    /**
     * @ORM\Column(type="string")
     */
    protected $value;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $active = 1;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $winning = 1;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

	/**
     * @ORM\OneToOne(targetEntity="Entry")
   	 * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Prize")
     * @ORM\JoinColumn(name="prize_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $prize;

    /**
     * @ORM\Column(type="datetime", name="created_at")
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", name="updated_at")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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
    public function getInstantwin()
    {
        return $this->instantwin;
    }

    /**
     * @param unknown_type $instantwin
     */
    public function setInstantwin($instantwin)
    {
        $this->instantwin = $instantwin;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getPrize()
    {
    	return $this->prize;
    }

    /**
     * @param unknown_type $prize
     */
    public function setPrize($prize)
    {
    	$this->prize = $prize;

    	return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Datetime $value
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param unknown_type $active
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getWinning()
    {
        return $this->winning;
    }

    /**
     * @param unknown_type $winning
     */
    public function setWinning($winning)
    {
        $this->winning = $winning;

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
    }

    /**
     * @return PlaygroundGame\Entity\Entry
     */
    public function getEntry()
    {
    	return $this->entry;
    }

    /**
     * @param PlaygroundGame\Entity\Entry $entry
     */
    public function setEntry($entry)
    {
    	$this->entry = $entry;

    	return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param unknown_type $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param unknown_type $updatedAt
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
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        $this->value = (isset($data['value']) && $data['value'] != null) ? $data['value'] : null;
        $this->active = (isset($data['active']) && $data['active'] != null) ? $data['active'] : null;
        $this->winning = (isset($data['winning']) && $data['winning'] != null) ? $data['winning'] : null;
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
                'name' => 'active',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'winning',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
            		'name' => 'prize',
            		'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
