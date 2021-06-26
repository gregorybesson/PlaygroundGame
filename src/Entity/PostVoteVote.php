<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_postvote_vote")
 */
class PostVoteVote implements InputFilterAwareInterface, \JsonSerializable
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="PostVotePost", inversedBy="votes")
     * @ORM\JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $post;

    /**
     * @ORM\ManyToOne(targetEntity="PostVote")
     **/
    protected $postvote;

    /**
     * @ORM\ManyToOne(targetEntity="PostVoteComment", inversedBy="votes")
     * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="CASCADE")
     **/
    protected $postComment;

    /**
     * @ORM\ManyToOne(targetEntity="PlaygroundUser\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", onDelete="CASCADE")
     */
    protected $user;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $ip;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $note;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /** @PrePersist */
    public function createChrono()
    {
        $this->createdAt = new \DateTime("now");
        $this->updatedAt = new \DateTime("now");
    }

    /** @PreUpdate */
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
     * @return string unknown_type
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

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
     * @return the unknown_type
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param unknown_type $post
     */
    public function setPost($post, $isAVoteForPost = false)
    {
        // echo get_class($post);
        // echo $post->getId();
        // die('---');
        // Check that there is no drawback using the cascading update from PostVoteEntry
        if ($isAVoteForPost) {
            $post->addVote($this);
        }

        $this->post = $post;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getPostComment()
    {
        return $this->postComment;
    }

    /**
     * @param unknown_type $postComment
     */
    public function setPostComment($postComment)
    {
        // Check that there is no drawback using the cascading update from PostVoteEntry
        $postComment->addVote($this);
        $this->postComment = $postComment;

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
        $this->postvote = $postvote;

        return $this;
    }

    /**
    * @return the unknown_type
    */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param unknown_type $message
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
    * @return the unknown_type
    */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param unknown_type $note
     */
    public function setNote($note)
    {
        $this->note = $note;

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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
