<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
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
 * @ORM\Table(name="game_quiz_answer")
 */
class QuizAnswer implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="QuizQuestion", inversedBy="answers")
     *
     **/
    protected $question;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $answer;

    /**
     * Explanation of the answer
     * @ORM\Column(type="text", nullable=true)
     */
    protected $explanation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $video;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $image;

    /**
     * The answer score in the game
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $points = 0;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $position = 0;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $correct = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated_at;

    /** @PrePersist */
    public function createChrono()
    {
        $this->created_at = new \DateTime("now");
        $this->updated_at = new \DateTime("now");
    }

    /** @PreUpdate */
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
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @param unknown_type $question
     */
    public function setQuestion($question)
    {
        // Check that there is no drawback using the cascading update from QuizQuestion : addAnswers()
        //$question->addAnswer($this);
        $this->question = $question;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param unknown_type $answer
     */
    public function setAnswer($answer)
    {
        $this->answer = $answer;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getExplanation()
    {
        return $this->explanation;
    }

    /**
     * @param unknown_type $explanation
     */
    public function setExplanation($explanation)
    {
        $this->explanation = $explanation;

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
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param unknown_type $points
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param unknown_type $position
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCorrect()
    {
        return $this->correct;
    }

    /**
     * @param unknown_type $correct
     */
    public function setCorrect($correct)
    {
        $this->correct = $correct;

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
        if (isset($data['answer']) && $data['answer'] != null) {
            $this->answer = $data['answer'];
        }

        if (isset($data['explanation']) && $data['explanation'] != null) {
            $this->explanation = $data['explanation'];
        }

        if (isset($data['type']) && $data['type'] != null) {
            $this->type = $data['type'];
        }

        if (isset($data['position']) && $data['position'] != null) {
            $this->position = $data['position'];
        }

        if (isset($data['image']) && $data['image'] != null) {
            $this->image = $data['image'];
        }

        if (isset($data['video']) && $data['video'] != null) {
            $this->video = $data['video'];
        }

        if (isset($data['points']) && $data['points'] != null) {
            $this->points = $data['points'];
        }

        if (isset($data['correct']) && $data['correct'] != null) {
            $this->correct = $data['correct'];
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
                'required'   => true,
                'filters' => array(
                    array('name'    => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'answer',
                'required' => true,
            )));
            
            $inputFilter->add($factory->createInput(array(
                'name'     => 'position',
                'required' => true,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'explanation',
                'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'correct',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'Between',
                        'options' => array(
                            'min'      => 0,
                            'max'      => 1,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                    'name'     => 'video',
                    'required' => false,
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'image',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
