<?php

namespace PlaygroundGame\Entity;

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
 * @ORM\Table(name="game_quiz_reply_answer")
 */
class QuizReplyAnswer implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="QuizReply", inversedBy="answers")
     * @ORM\JoinColumn(name="reply_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $reply;

    /**
     * @ORM\Column(type="integer")
     **/
    protected $question_id;

    /**
     * @ORM\Column(type="string")
     **/
    protected $question;

    /**
     * @ORM\Column(type="integer")
     **/
    protected $answer_id;

    /**
     * @ORM\Column(type="string")
     **/
    protected $answer;

    /**
     * @ORM\Column(type="integer")
     **/
    protected $points;

    /**
     * @ORM\Column(type="boolean", length=255, nullable=false)
     */
    protected $correct;

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
     * @return PlaygroundGame\Entity\Reply
     */
    public function getReply()
    {
        return $this->reply;
    }

    /**
     * @param PlaygroundGame\Entity\Reply $reply
     */
    public function setReply($reply)
    {
        $this->reply = $reply;

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
    public function getQuestionId()
    {
        return $this->question_id;
    }

    /**
     * @param string $questionId
     */
    public function setQuestionId($questionId)
    {
        $this->question_id = $questionId;

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
    public function getAnswerId()
    {
        return $this->answer_id;
    }

    /**
     * @param unknown_type $answer
     */
    public function setAnswerId($answerId)
    {
        $this->answer_id = $answerId;

        return $this;
    }

    /**
     * @return the $points
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param field_type $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
