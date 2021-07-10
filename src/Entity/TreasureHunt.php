<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_treasurehunt")
 */
class TreasureHunt extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'treasurehunt';

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $winners = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $timer = 0;

    /**
     * @ORM\Column(name="timer_duration", type="integer", nullable=false)
     */
    protected $timerDuration = 0;

    /**
     * if 0 You can't replay when you loose the puzzle. you go to the next one directly
     * if 1 Go to the next puzzle only when the actual one is won
     *
     * @ORM\Column(name="replay_puzzle", type="integer", nullable=false)
     */
    protected $replayPuzzle = 1;

    /**
     * @ORM\OneToMany(targetEntity="TreasureHuntPuzzle", mappedBy="treasurehunt")
     **/
    protected $puzzles;

    public function __construct()
    {
    	parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->puzzles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param unknown_type $puzzles
     */
    public function setPuzzles($puzzles)
    {
    	$this->puzzles = $puzzles;

    	return $this;
    }

    /**
     * Get puzzle.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPuzzles()
    {
    	return $this->puzzles;
    }

    /**
     * Get puzzle.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPuzzle($id)
    {
        return $this->puzzles[$id];
    }

    /**
     * Add a puzzle to the hunt.
     *
     *
     * @return void
     */
    public function addPuzzle($puzzle)
    {
    	$this->puzzles[] = $puzzle;
    }

	/**
	 * @return the $winners
	 */
	public function getWinners()
	{
		return $this->winners;
	}

	/**
	 * @param field_type $winners
	 */
	public function setWinners($winners)
	{
		$this->winners = $winners;

		return $this;
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
	 * @return the $timerDuration
	 */
	public function getTimerDuration()
	{
		return $this->timerDuration;
	}

	/**
	 * @param number $timerDuration
	 */
	public function setTimerDuration($timerDuration)
	{
		$this->timerDuration = $timerDuration;

		return $this;
	}

	/**
     * @return the $replayPuzzle
     */
    public function getReplayPuzzle()
    {
        return $this->replayPuzzle;
    }

	/**
     * @param number $replayPuzzle
     */
    public function setReplayPuzzle($replayPuzzle)
    {
        $this->replayPuzzle = $replayPuzzle;
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

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array())
    {
        parent::populate($data);
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

            $inputFilter = parent::getInputFilter();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'replayPuzzle',
                'required' => false,
                'validators' => array(
                    array('name'    => 'Digits',),
                    array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
