<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_treasurehunt")
 */
class TreasureHunt extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'treasurehunt';
    
    /**
     * Who can participate :
     * 'all' : Everybody can play
     * 'prospect' : Only unregistered users of the site can play
     * 'customer' : Only registered users of the site can play
     *
     * @ORM\Column(name="player_type", type="string", nullable=false)
     */
    protected $playerType = 'all';
    
    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $winners;
    
    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $timer = 0;
    
    /**
     * @ORM\Column(name="timer_duration", type="integer", nullable=false)
     */
    protected $timerDuration = 0;
    
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
	 * @return the $playerType
	 */
	public function getPlayerType() {
		return $this->playerType;
	}

	/**
	 * @param string $playerType
	 */
	public function setPlayerType($playerType) {
		$this->playerType = $playerType;
	}

	/**
	 * @return the $winners
	 */
	public function getWinners() {
		return $this->winners;
	}

	/**
	 * @param field_type $winners
	 */
	public function setWinners($winners) {
		$this->winners = $winners;
	}

	/**
	 * @return the $timer
	 */
	public function getTimer() {
		return $this->timer;
	}

	/**
	 * @param number $timer
	 */
	public function setTimer($timer) {
		$this->timer = $timer;
	}

	/**
	 * @return the $timerDuration
	 */
	public function getTimerDuration() {
		return $this->timerDuration;
	}

	/**
	 * @param number $timerDuration
	 */
	public function setTimerDuration($timerDuration) {
		$this->timerDuration = $timerDuration;
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
