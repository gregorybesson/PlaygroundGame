<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_instantwin")
 */
class InstantWin extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'instantwin';

    /**
     * values : datetime - dates of win
     *          code     - winning and loosing codes are registered for the game
     *          visit    - a winning is triggered every n visits,
     *          random   - Not set by default : The number of wins is set and it's triggered randomly
     *          based on number of visitors + duration of game + number of wins
     *
     * @ORM\Column(name="occurrence_type", type="string", nullable=false)
     */
    protected $occurrenceType = 'datetime';

    /**
     * Automatic Draw
     * @ORM\Column(name="schedule_occurrence_auto", type="boolean", nullable=false)
     */
    protected $scheduleOccurrenceAuto = 0;

    /**
     * Determine how much occurrences to create
     *
     * @ORM\Column(name="occurrence_number", type="integer", nullable=true)
     */
    protected $occurrenceNumber;    

    /**
     * Determine how much winning occurrences to create among the occurrences
     *
     * @ORM\Column(name="winning_occurrence_number", type="integer", nullable=true)
     */
    protected $winningOccurrenceNumber;
    
    /**
     * this field is taken into account only if $occurrenceNumber<>0.
     * if 'game' $occurrenceNumber are drawn between game start date and game end date
     * if 'hour' $occurrenceNumber are drawn a hour between game start date and game end date
     * if 'day' $occurrenceNumber are drawn a day for each day between game start date and game end date
     * if 'week' $occurrenceNumber are drawn a week between game start date and game end date
     * if 'month' $occurrenceNumber are drawn a month between game start date and game end date
     *
     * @ORM\Column(name="occurrence_draw_frequency", type="string", nullable=true)
     */
    protected $occurrenceDrawFrequency;

    /**
     * @ORM\Column(name="scratchcard_image", type="string", length=255, nullable=true)
     */
    protected $scratchcardImage;

    /**
     * @ORM\OneToMany(targetEntity="InstantWinOccurrence", mappedBy="instant_win")
     **/
    private $occurrences;

    public function __construct()
    {
    	parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->occurrences = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param unknown_type $questions
     */
    public function setOccurrences($occurrences)
    {
        $this->occurrences = $occurrences;

        return $this;
    }

    /**
     * Get question.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOccurrences()
    {
        return $this->occurrences;
    }

    /**
     * Add a question to the quiz.
     *
     * @param QuizQuestion $question
     *
     * @return void
     */
    public function addOccurrence($occurrence)
    {
        $this->occurrences[] = $occurrence;
    }

    /**
     * @return the unknown_type
     */
    public function getOccurrenceType()
    {
        return $this->occurrenceType;
    }

    /**
     * @param unknown_type $occurrenceType
     */
    public function setOccurrenceType($occurrenceType)
    {
        $this->occurrenceType = $occurrenceType;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getOccurrenceNumber()
    {
        return $this->occurrenceNumber;
    }

    /**
     * @param unknown_type $occurrenceNumber
     */
    public function setOccurrenceNumber($occurrenceNumber)
    {
        $this->occurrenceNumber = $occurrenceNumber;

        return $this;
    }
    /**
     * @return the unknown_type
     */
    public function getWinningOccurrenceNumber()
    {
        return $this->winningOccurrenceNumber;
    }

    /**
     * @param unknown_type $occurrenceNumber
     */
    public function setWinningOccurrenceNumber($winningOccurrenceNumber)
    {
        $this->winningOccurrenceNumber = $winningOccurrenceNumber;

        return $this;
    }
    
    /**
     * @return the unknown_type
     */
    public function getOccurrenceDrawFrequency()
    {
    	return $this->occurrenceDrawFrequency;
    }
    
    /**
     * @param unknown_type $occurrenceDrawFrequency
     */
    public function setOccurrenceDrawFrequency($occurrenceDrawFrequency)
    {
    	$this->occurrenceDrawFrequency = $occurrenceDrawFrequency;
    
    	return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getScheduleOccurrenceAuto()
    {
        return $this->scheduleOccurrenceAuto;
    }

    /**
     * @param unknown_type $scheduleOccurrenceAuto
     */
    public function setScheduleOccurrenceAuto($scheduleOccurrenceAuto)
    {
        $this->scheduleOccurrenceAuto = $scheduleOccurrenceAuto;

        return $this;
    }

    /**
     *
     * @return the $scratchcardImage
     */
    public function getScratchcardImage ()
    {
        return $this->scratchcardImage;
    }

    /**
     *
     * @param field_type $scratchcardImage
     */
    public function setScratchcardImage ($scratchcardImage)
    {
        $this->scratchcardImage = $scratchcardImage;
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

        if (isset($data['scheduleOccurrenceAuto']) && $data['scheduleOccurrenceAuto'] != null) {
            $this->scheduleOccurrenceAuto = $data['scheduleOccurrenceAuto'];
        }

        if (isset($data['occurrenceNumber']) && $data['occurrenceNumber'] != null) {
            $this->occurrenceNumber = $data['occurrenceNumber'];
        }

        if (isset($data['winningOccurrenceNumber']) && $data['winningOccurrenceNumber'] != null) {
            $this->occurrenceNumber = $data['winningOccurrenceNumber'];
        }

        if (isset($data['occurrenceType']) && $data['occurrenceType'] != null) {
            $this->occurrenceType = $data['occurrenceType'];
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

            $inputFilter = parent::getInputFilter();

            $inputFilter->add($factory->createInput(array('name' => 'id', 'required' => true, 'filters' => array(array('name' => 'Int'),),)));

            $inputFilter->add($factory->createInput(array(
            	'name' => 'occurrenceNumber', 
            	'required' => false, 
            	'validators' => array(
            		array('name' => 'Digits',),
            	),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'winningOccurrenceNumber', 
                'required' => false, 
                'validators' => array(
                    array('name' => 'Digits',),
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
           		'name' => 'occurrenceDrawFrequency',
           		'required' => false,
           		'validators' => array(
       				array(
      					'name' => 'InArray',
           				'options' => array(
           					'haystack' => array('hour', 'day', 'week', 'month', 'game'),
            			),
            		),
           		),
            )));

            $inputFilter->add($factory->createInput(array(
                'name' => 'occurrenceType',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'InArray',
                        'options' => array(
                            'haystack' => array('datetime', 'code', 'visitor', 'random'),
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}