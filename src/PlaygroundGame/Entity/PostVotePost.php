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
 * @ORM\Table(name="game_postvote_post")
 */
class PostVotePost implements InputFilterAwareInterface, \JsonSerializable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PostVote", inversedBy="posts")
     * @ORM\JoinColumn(name="postvote_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $postvote;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id")
     **/
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $entry;

    /**
     * @ORM\OneToMany(targetEntity="PostVoteVote", mappedBy="post")
     */
    private $votes;

    /**
     * @ORM\OneToMany(targetEntity="PostVoteComment", mappedBy="post")
     */
    private $comments;    

    /**
     * @ORM\OneToMany(targetEntity="PostVotePostElement", mappedBy="post", cascade={"persist","remove"})
     */
    private $postElements;

    /**
     * values :
     *          0 : draft
     *          1 : user confirmed
     *          2 : admin accepted
     *          9 : admin rejected
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $status = 0;
    
   /**
    * @ORM\Column(type="boolean", nullable=true)
    */
    protected $pushed = 0;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->postElements = new ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
    public function getPostvote()
    {
        return $this->postvote;
    }

    /**
     * @param unknown_type $postvote
     */
    public function setPostvote($postvote)
    {
        $postvote->addPost($this);
        $this->postvote = $postvote;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param unknown_type $user
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param boolean $entry
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Add an entry to the post.
     *
     * @param PostVotePostEntry $postElement
     *
     * @return void
     */
    public function addPostElement($postElement)
    {
        $postElement->setPost($this);
        $this->postElements[] = $postElement;
    }

    /**
     * @return ArrayCollection unknown_type
     */
    public function getPostElements()
    {
        return $this->postElements;
    }

    /**
     */
    public function setPostElements($postElements)
    {
        $this->postElements = $postElements;

        return $this;
    }

    /**
     * Add an entry to the vote.
     *
     * @param PostVoteVote $vote
     *
     * @return void
     */
    public function addVote($vote)
    {
        $this->votes[] = $vote;
    }

    /**
     * @return the unknown_type
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param unknown_type $votes
     */
    public function setVotes($votes)
    {
        $this->votes = $votes;

        return $this;
    }

    /**
     * Add an entry to the comment.
     *
     * @param PostVoteComment $comment
     *
     * @return void
     */
    public function addComment(PostVoteComment $comment)
    {
        //$comment->setPost($this);
        $this->comments[] = $comment;
    }

    /**
     * @return the unknown_type
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param unknown_type $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    public function addComments(ArrayCollection $comments)
    {
        foreach ($comments as $comment) {
            $comment->setPost($this);
            $this->comments->add($comment);
        }
    }

    public function removeComments(\Doctrine\Common\Collections\Collection $comments)
    {
        foreach ($comments as $comment) {
            $comment->setPost(null);
            $this->comments->removeElement($comment);
        }
    }

    /**
     * @return integer unknown_type
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param unknown_status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
    
   /**
    * @return integer
    */
    public function isPushed()
    {
        return $this->pushed;
    }
        
   /**
    * @param bool $pushed
    * @return PostVotePost
    */
    public function setPushed($pushed)
    {
        $this->pushed = (boolean)$pushed;
        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param unknown_type $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param unknown_type $updatedAt
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

        if (isset($obj_vars['postElements'])) {
            $obj_vars['postElements'] = $this->getPostElements()->toArray();
        }

        return $obj_vars;
    }

    /**
     * Convert the object to json.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->getArrayCopy();
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

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'id',
                        'required' => true,
                        'filters' => array(array('name' => 'Int'))
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
