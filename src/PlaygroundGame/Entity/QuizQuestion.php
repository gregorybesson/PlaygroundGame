<?php

namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
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
 * @ORM\Table(name="game_quiz_question")
 */
class QuizQuestion implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Quiz", inversedBy="questions")
     */
    protected $quiz;

    /**
     * @ORM\OneToMany(targetEntity="QuizAnswer", mappedBy="question", cascade={"persist","remove"})
     */
    private $answers;

    /**
     * values :
     *          0 : closed (you can select only one answer)
     *          1 : opened (you can select many answers),
     *          2: No answer to select. You write a text in a textarea
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $type = 0;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $question;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $video;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $audio = 0;
    
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $autoplay = false;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $hint;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $prediction = 0;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $timer = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $timer_duration = 0;

    /**
     * The question weight in the game
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $weight = 1;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $max_points = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $max_correct_answers = 0;

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
    public function getQuiz()
    {
        return $this->quiz;
    }

    /**
     * @param unknown_type $quiz
     */
    public function setQuiz($quiz)
    {
        $quiz->addQuestion($this);
        $this->quiz = $quiz;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getAnswers()
    {
        return $this->answers;
    }

    /**
     * frm collection solution
     * @param unknown_type $answers
     */
    public function setAnswers(ArrayCollection $answers)
    {
        $this->answers = $answers;

        return $this;
    }

    public function addAnswers(ArrayCollection $answers)
    {
        foreach ($answers as $answer) {
            $answer->setQuestion($this);
            $this->answers->add($answer);
        }
    }

    public function removeAnswers(ArrayCollection $answers)
    {
        foreach ($answers as $answer) {
            $answer->setQuestion(null);
            $this->answers->removeElement($answer);
        }
    }

    /**
     * Add an answer to the quiz.
     *
     * @param QuizAnswer $answer
     *
     * @return void
     */
    public function addAnswer($answer)
    {
        $this->answers[] = $answer;
    }

    /**
     * @return the unknown_type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param unknown_type $type
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getVideo()
    {
        return $this->video;
    }

    /**
     * @param unknown_type $video
     */
    public function setVideo($video)
    {
        $this->video = $video;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param unknown_type $image
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }
    
    /**
     * @return the unknown_type
     */
    public function getAudio()
    {
        return $this->audio;
    }
    
    /**
     * @param unknown_type audio
     */
    public function setAudio($audio)
    {
        $this->audio = $audio;
    
        return $this;
    }
    
    /**
     * @return the unknown_type
     */
    public function getAutoplay()
    {
        return $this->autoplay;
    }
    
    /**
     * @param unknown_type autoplay
     */
    public function setAutoplay($autoplay)
    {
        $this->autoplay = $autoplay;
    
        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getHint()
    {
        return $this->hint;
    }

    /**
     * @param unknown_type $hint
     */
    public function setHint($hint)
    {
        $this->hint = $hint;

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
    public function getPrediction()
    {
        return $this->prediction;
    }

    /**
     * @param unknown_type $prediction
     */
    public function setPrediction($prediction)
    {
        $this->prediction = $prediction;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getTimerDuration()
    {
        return $this->timer_duration;
    }

    /**
     * @param unknown_type $timer_duration
     */
    public function setTimerDuration($timer_duration)
    {
        $this->timer_duration = $timer_duration;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param unknown_type $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxPoints()
    {
        return $this->max_points;
    }

    /**
     * @param integer max_points
     */
    public function setMaxPoints($max_points)
    {
        $this->max_points = $max_points;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMaxCorrectAnswers()
    {
        return $this->max_correct_answers;
    }

    /**
     * @param integer max_correct_answers
     */
    public function setMaxCorrectAnswers($max_correct_answers)
    {
        $this->max_correct_answers = $max_correct_answers;

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
     * @return string
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition($position)
    {
        $this->position = $position;

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
        if (isset($data['question']) && $data['question'] != null) {
            $this->question = $data['question'];
        }

        if (isset($data['hint']) && $data['hint'] != null) {
            $this->hint = $data['hint'];
        }

        if (isset($data['type']) && $data['type'] != null) {
            $this->type = $data['type'];
        }

        if (isset($data['position']) && $data['position'] != null) {
            $this->position = $data['position'];
        }

        if (isset($data['weight']) && $data['weight'] != null) {
            $this->weight = $data['weight'];
        }

        if (isset($data['image']) && $data['image'] != null) {
            $this->image = $data['image'];
        }

        if (isset($data['video']) && $data['video'] != null) {
            $this->video = $data['video'];
        }
        
        if (isset($data['audio']) && $data['audio'] != null) {
            $this->audio = $data['audio'];
        }
        
        if (isset($data['autoplay']) && $data['autoplay'] != null) {
            $this->autoplay = $data['autoplay'];
        }

        if (isset($data['timer']) && $data['timer'] != null) {
            $this->timer = $data['timer'];
        }

        if (isset($data['timer_duration']) && $data['timer_duration'] != null) {
            $this->timer_duration = $data['timer_duration'];
        }

        if (isset($data['max_points']) && $data['max_points'] != null) {
            $this->max_points = $data['max_points'];
        }

        if (isset($data['max_correct_answers']) && $data['max_correct_answers'] != null) {
            $this->max_correct_answers = $data['max_correct_answers'];
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
                'name'       => 'id',
                'required'   => false,
            	'allowEmpty' => true,
                'filters'    => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'question',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'hint',
                'required' => false,
                /*'filters'  => array(
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),*/
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'       => 'timer',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => array(
                    array('name' => 'Boolean'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'type',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'Between',
                        'options' => array(
                            'min'      => 0,
                            'max'      => 2,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'image',
                'required' => false,
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'audio',
                'required' => false,
                'filters' => array(
                    array('name' => 'Int')
                ),
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'       => 'autoplay',
                'required'   => false,
                'allowEmpty' => true,
                'filters'    => array(
                    array('name' => 'Boolean'),
                ),
            )));
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
