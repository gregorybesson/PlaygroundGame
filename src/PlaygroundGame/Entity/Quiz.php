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
 * @ORM\Table(name="game_quiz")
 */
class Quiz extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'quiz';

    /**
     * Automatic Draw
     * @ORM\Column(name="draw_auto", type="boolean", nullable=false)
     */
    protected $drawAuto = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $winners;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $substitutes;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $timer = 0;

    /**
     * @ORM\Column(name="timer_duration", type="integer", nullable=false)
     */
    protected $timerDuration = 0;

    /**
     * @ORM\Column(name="question_grouping", type="integer", nullable=false)
     */
    protected $questionGrouping = 0;

    /**
     * @ORM\Column(name="victory_conditions", type="integer", nullable=false)
     */
    protected $victoryConditions = 0;

    /**
     * @ORM\Column(name="max_points", type="integer", nullable=true)
     */
    protected $maxPoints = 0;

    /**
     * @ORM\Column(name="max_correct_answers", type="integer", nullable=true)
     */
    protected $maxCorrectAnswers = 0;

    /**
     * @ORM\OneToMany(targetEntity="QuizQuestion", mappedBy="quiz", cascade={"persist","remove"})
     * @ORM\OrderBy({"position" = "ASC"})
     **/
    private $questions;

    public function __construct()
    {
    	parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->questions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getDrawAuto()
    {
        return $this->drawAuto;
    }

    /**
     * @param integer $drawAuto
     */
    public function setDrawAuto($drawAuto)
    {
        $this->drawAuto = $drawAuto;

        return $this;
    }

    /**
     * @return integer
     */
    public function getWinners()
    {
        return $this->winners;
    }

    /**
     * @param integer $winners
     */
    public function setWinners($winners)
    {
        $this->winners = $winners;

        return $this;
    }

    /**
     * @return integer
     */
    public function getSubstitutes()
    {
        return $this->substitutes;
    }

    /**
     * @param integer $substitutes
     */
    public function setSubstitutes($substitutes)
    {
        $this->substitutes = $substitutes;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getTimer()
    {
        return $this->timer;
    }

    /**
     * @param unknown_type $timer
     */
    public function setTimer($timer)
    {
        $this->timer = $timer;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getTimerDuration()
    {
        return $this->timerDuration;
    }

    /**
     * @param unknown_type $timerDuration
     */
    public function setTimerDuration($timerDuration)
    {
        $this->timerDuration = $timerDuration;

        return $this;
    }

    public function getVictoryConditions()
    {
        return $this->victoryConditions;
    }

    /**
     * @param unknown_type $timerDuration
     */
    public function setVictoryConditions($victoryConditions)
    {
        $this->victoryConditions = $victoryConditions;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getQuestionGrouping()
    {
        return $this->questionGrouping;
    }

    /**
     * @param unknown_type $questionGrouping
     */
    public function setQuestionGrouping($questionGrouping)
    {
        $this->questionGrouping = $questionGrouping;

        return $this;
    }

    /**
     * @param unknown_type $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxPoints()
    {
        return $this->maxPoints;
    }

    /**
     * @param integer maxPoints
     */
    public function setMaxPoints($maxPoints)
    {
        $this->maxPoints = $maxPoints;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxCorrectAnswers()
    {
        return $this->maxCorrectAnswers;
    }

    /**
     * @param integer maxCorrectAnswers
     */
    public function setMaxCorrectAnswers($maxCorrectAnswers)
    {
        $this->maxCorrectAnswers = $maxCorrectAnswers;

        return $this;
    }

    /**
     * Get question.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * Add a question to the quiz.
     *
     * @param QuizQuestion $question
     *
     * @return void
     */
    public function addQuestion($question)
    {
        $this->questions[] = $question;
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

        if (isset($data['timer']) && $data['timer'] != null) {
            $this->timer = $data['timer'];
        }
        if (isset($data['timerDuration']) && $data['timerDuration'] != null) {
            $this->timerDuration = $data['timerDuration'];
        }
        if (isset($data['questionGrouping']) && $data['questionGrouping'] != null) {
            $this->questionGrouping = $data['questionGrouping'];
        }

        if (isset($data['maxPoints']) && $data['maxPoints'] != null) {
            $this->maxPoints = $data['maxPoints'];
        }

        if (isset($data['maxCorrectAnswers']) && $data['maxCorrectAnswers'] != null) {
            $this->maxCorrectAnswers = $data['maxCorrectAnswers'];
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

            $inputFilter->add($factory->createInput(array(
                    'name'       => 'id',
                    'required'   => true,
                    'filters' => array(
                        array('name' => 'Int'),
                    ),
            )));

            $inputFilter->add($factory->createInput(array(
                   'name'     => 'title',
                   'required' => true,
                   'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                           'name'    => 'StringLength',
                           'options' => array(
                             'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 255,

                           ),
                      ),
                   ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'identifier',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                    array('name' => 'PlaygroundCore\Filter\Slugify'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 5,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
