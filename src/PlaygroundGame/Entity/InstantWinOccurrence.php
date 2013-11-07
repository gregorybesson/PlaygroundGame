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
    protected $occurrence_value;

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
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
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
    public function getOccurrenceValue()
    {
        return $this->occurrence_value;
    }

    /**
     * @param Datetime $occurrence_value
     */
    public function setOccurrenceValue($occurrence_value)
    {
        $this->occurrence_value = $occurrence_value;

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

        // if (isset($obj_vars['occurrence_date']) && $obj_vars['occurrence_date'] != null) {
        //     $obj_vars['occurrence_date'] = $obj_vars['occurrence_date']->format('d/m/Y H:i');
        // }

        return $obj_vars;
    }

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        $this->occurrence_value = (isset($data['occurrence_value']) && $data['occurrence_value'] != null) ? $data['occurrence_value'] : null;
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
