<?php

namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_crossword_word")
 * @Gedmo\TranslationEntity(class="PlaygroundGame\Entity\GameTranslation")
 */

class CrosswordWord implements InputFilterAwareInterface, \JsonSerializable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Crossword", inversedBy="words")
     * @ORM\JoinColumn(name="crossword_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $game;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $solution;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $clue;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="layout_row", type="integer", nullable=true)
     */
    protected $layoutRow;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="layout_column", type="integer", nullable=true)
     */
    protected $layoutColumn;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $position;

    /**
     * @Gedmo\Translatable
     * values: accross or down
     * @ORM\Column(type="string", nullable=true)
     */
    protected $orientation;

    /**
     * The word score in the game
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points = 0;

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
     * Gets the value of solution.
     *
     * @return mixed
     */
    public function getSolution()
    {
        return $this->solution;
    }

    /**
     * Sets the value of solution.
     *
     * @param mixed $solution the solution
     *
     * @return self
     */
    public function setSolution($solution)
    {
        $this->solution = $solution;

        return $this;
    }

    /**
     * Gets the value of clue.
     *
     * @return mixed
     */
    public function getClue()
    {
        return $this->clue;
    }

    /**
     * Sets the value of clue.
     *
     * @param mixed $clue the clue
     *
     * @return self
     */
    public function setClue($clue)
    {
        $this->clue = $clue;

        return $this;
    }

    /**
     * Gets the value of layoutRow.
     *
     * @return mixed
     */
    public function getLayoutRow()
    {
        return $this->layoutRow;
    }

    /**
     * Sets the value of layoutRow.
     *
     * @param mixed $layoutRow the layoutRow
     *
     * @return self
     */
    public function setLayoutRow($layoutRow)
    {
        $this->layoutRow = $layoutRow;

        return $this;
    }

    /**
     * Gets the value of layoutColumn.
     *
     * @return mixed
     */
    public function getLayoutColumn()
    {
        return $this->layoutColumn;
    }

    /**
     * Sets the value of layoutColumn.
     *
     * @param mixed $layoutColumn the layoutColumn
     *
     * @return self
     */
    public function setLayoutColumn($layoutColumn)
    {
        $this->layoutColumn = $layoutColumn;

        return $this;
    }

    /**
     * Gets the value of position.
     *
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets the value of position.
     *
     * @param mixed $position the position
     *
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Gets the value of orientation.
     *
     * @return mixed
     */
    public function getOrientation()
    {
        return $this->orientation;
    }

    /**
     * Sets the value of orientation.
     *
     * @param mixed $orientation the orientation
     *
     * @return self
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * @return integer $points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;

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
    public function jsonSerialize(): mixed
    {
        $jsonArray = $this->getArrayCopy();
        unset($jsonArray['inputFilter']);
        unset($jsonArray['__initializer__']);
        unset($jsonArray['__cloner__']);
        unset($jsonArray['__isInitialized__']);
        unset($jsonArray['game']);
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
        if (isset($data['solution']) && $data['solution'] !== null) {
            $this->solution = $data['solution'];
        }

        if (isset($data['clue']) && $data['clue'] !== null) {
            $this->clue = $data['clue'];
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

            $inputFilter->add(
                $factory->createInput([
                    'name' => 'orientation',
                    'required' => false,
                ])
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
