<?php

namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_treasurehunt_score")
 */
class TreasureHuntScore implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $entry;

    /**
     * @ORM\Column(type="integer")
     **/
    protected $mistakes = 0;

    /**
     * duration of the game, in seconds
     * @ORM\Column(type="integer")
     **/
    protected $duration = 0;

    /**
     * A JSON encoded array of puzzles and pieces of the puzzle
     * including time spent, mistakes made etc
     * @ORM\Column(type="text")
     **/
    protected $jsonPuzzles;

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
        $this->puzzles = new ArrayCollection();
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
     * @return the $mistakes
     */
    public function getMistakes()
    {
        return $this->mistakes;
    }

    /**
     * @param field_type $mistakes
     */
    public function setMistakes($mistakes)
    {
        $this->mistakes = $mistakes;

        return $this;
    }

    /**
     * @return the $jsonPuzzles
     */
    public function getJsonPuzzles()
    {
        return $this->jsonPuzzles;
    }

    /**
     * @param field_type $jsonPuzzles
     */
    public function setJsonPuzzles($jsonPuzzles)
    {
        $this->jsonPuzzles = $jsonPuzzles;

        return $this;
    }

    /**
     * @return integer $duration
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param integer $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;

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
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
