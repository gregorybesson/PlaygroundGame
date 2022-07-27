<?php
namespace PlaygroundGame\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

use PlaygroundGame\Entity\Game;
use Gedmo\Mapping\Annotation as Gedmo;

use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_crossword")
 */

class Crossword extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'crossword';

    /**
     * the words associated with the game
     * @ORM\OneToMany(targetEntity="CrosswordWord", mappedBy="game")
     * @ORM\OrderBy({"position"="ASC"})
     */
    protected $words;

    /**
     * @ORM\Column(name="victory_conditions", type="integer", nullable=false)
     */
    protected $victoryConditions = 0;

    /**
     * values : crossword - A regular crossword
     *          word_search - A wordsearch puzzle
     *          wordle - A wordle puzzle
     *          hangman - A Hangman puzzle
     *
     * @ORM\Column(name="game_type", type="string", nullable=false)
     */
    protected $gameType = 'crossword';

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="layout_rows", type="integer", nullable=true)
     */
    protected $layoutRows;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="layout_columns", type="integer", nullable=true)
     */
    protected $layoutColumns;

    public function __construct()
    {
        parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->words = new ArrayCollection();
    }

    /**
     * Gets the the words associated with the game.
     *
     * @return mixed
     */
    public function getWords()
    {
        return $this->words;
    }

    /**
     * Sets the the words associated with the game.
     *
     * @param mixed $words the words
     *
     * @return self
     */
    protected function setWords($words)
    {
        $this->words = $words;

        return $this;
    }

    /**
     * Add a word to the trading word.
     *
     *
     * @return void
     */
    public function addWord($word)
    {
        $this->words[] = $word;
    }

    public function getVictoryConditions()
    {
        return $this->victoryConditions;
    }

    /**
     */
    public function setVictoryConditions($victoryConditions)
    {
        $this->victoryConditions = $victoryConditions;

        return $this;
    }

    public function getGameType()
    {
        return $this->gameType;
    }

    public function setGameType($gameType)
    {
        $this->gameType = $gameType;

        return $this;
    }

    /**
     * Gets the value of layoutRows.
     *
     * @return mixed
     */
    public function getLayoutRows()
    {
        return $this->layoutRows;
    }

    /**
     * Sets the value of layoutRows.
     *
     * @param mixed $layoutRows the layoutRows
     *
     * @return self
     */
    public function setLayoutRows($layoutRows)
    {
        $this->layoutRows = $layoutRows;

        return $this;
    }

    /**
     * Gets the value of layoutColumns.
     *
     * @return mixed
     */
    public function getLayoutColumns()
    {
        return $this->layoutColumns;
    }

    /**
     * Sets the value of layoutColumns.
     *
     * @param mixed $layoutColumns the layoutColumns
     *
     * @return self
     */
    public function setLayoutColumns($layoutColumns)
    {
        $this->layoutColumns = $layoutColumns;

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
            $factory = new InputFactory();

            $inputFilter->add(
                $factory->createInput([
                    'name' => 'layoutRows',
                    'required' => false,
                ])
            );

            $inputFilter->add(
                $factory->createInput([
                    'name' => 'layoutColumns',
                    'required' => false,
                ])
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
