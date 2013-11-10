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
 * @ORM\Table(name="game_quiz_reply")
 */
class QuizReply implements InputFilterAwareInterface
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
    protected $totalCorrectAnswers;
    
    /**
     * @ORM\Column(type="integer")
     **/
    protected $maxCorrectAnswers;
    
    /**
     * @ORM\Column(type="integer")
     **/
    protected $totalQuestions;

    /**
     * @ORM\OneToMany(targetEntity="QuizReplyAnswer", mappedBy="reply", cascade={"persist","remove"})
     */
    private $answers;

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
     * @return the $totalCorrectAnswers
     */
    public function getTotalCorrectAnswers()
    {
        return $this->totalCorrectAnswers;
    }

	/**
     * @param field_type $totalCorrectAnswers
     */
    public function setTotalCorrectAnswers($totalCorrectAnswers)
    {
        $this->totalCorrectAnswers = $totalCorrectAnswers;
    }

	/**
     * @return the $maxCorrectAnswers
     */
    public function getMaxCorrectAnswers()
    {
        return $this->maxCorrectAnswers;
    }

	/**
     * @param field_type $maxCorrectAnswers
     */
    public function setMaxCorrectAnswers($maxCorrectAnswers)
    {
        $this->maxCorrectAnswers = $maxCorrectAnswers;
    }

	/**
     * @return the $totalQuestions
     */
    public function getTotalQuestions()
    {
        return $this->totalQuestions;
    }

	/**
     * @param field_type $totalQuestions
     */
    public function setTotalQuestions($totalQuestions)
    {
        $this->totalQuestions = $totalQuestions;
    }

	/**
     * @return the $answers
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
            $answer->setReply($this);
            $this->answers->add($answer);
        }
    }

    public function removeAnswers(ArrayCollection $answers)
    {
        foreach ($answers as $answer) {
            $answer->setReply(null);
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
    public function addAnswer(QuizReplyAnswer $answer)
    {
        $answer->setReply($this);
        $this->answers[] = $answer;
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
