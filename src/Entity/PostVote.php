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
 * @ORM\Table(name="game_postvote")
 */
class PostVote extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'postvote';

    /**
     * Display mode of Posts :
     * 'date' : sort by post date desc
     * 'random' : ...
     * 'votes' : sort by number of vote desc
     *
     * @ORM\Column(name="post_display_mode", type="string", nullable=false)
     */
    protected $postDisplayMode = 'date';
    
    /**
     * Number of Post displayed :
     * 0 : infinite
     *
     * @ORM\Column(name="post_display_number", type="integer", nullable=false)
     */
    protected $postDisplayNumber = 0;

    /**
     * Is it possible to vote ?
     * @ORM\Column(name="vote_active", type="boolean", nullable=false)
     */
    protected $voteActive = 1;

    /**
     * Is it possible to vote anonymously ?
     * @ORM\Column(name="vote_anonymous", type="boolean", nullable=false)
     */
    protected $voteAnonymous;

    /**
     * Type of moderation : moderate posts before their publication, or after their publication (default)
     * @ORM\Column(name="moderation_type", type="boolean", nullable=false, options={"default" = 0})
     */
    protected $moderationType = 0;

    /**
     * @ORM\OneToOne(targetEntity="PostVoteForm", mappedBy="postvote", cascade={"persist","remove"})
     **/
    private $form;

    /**
     * @ORM\OneToMany(targetEntity="PostVotePost", mappedBy="post_vote")
     **/
    private $posts;

    public function __construct()
    {
        parent::__construct();
        $this->setClassType(self::CLASSTYPE);
        $this->posts = new ArrayCollection();
    }

    /**
     * Add a post to the game
     *
     * @param PostVotePost $post
     *
     * @return void
     */
    public function addPost($post)
    {
        $this->post[] = $post;
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public function setPosts($posts)
    {
        $this->posts = $posts;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param unknown_type $form
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string unknown_type
     */
    public function getPostDisplayMode()
    {
        return $this->postDisplayMode;
    }

    /**
     * @param unknown_type $postDisplayMode
     */
    public function setPostDisplayMode($postDisplayMode)
    {
        $this->postDisplayMode = $postDisplayMode;

        return $this;
    }
    
    /**
     * @return int
     */
    public function getPostDisplayNumber()
    {
        return $this->postDisplayNumber;
    }
    
    /**
     * @param int $postDisplayNumber
     *
     * @return PostVote
     */
    public function setPostDisplayNumber($postDisplayNumber)
    {
        $this->postDisplayNumber = $postDisplayNumber;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getVoteActive()
    {
        return $this->voteActive;
    }

    /**
     * @param unknown_type $voteActive
     */
    public function setVoteActive($voteActive)
    {
        $this->voteActive = $voteActive;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getVoteAnonymous()
    {
        return $this->voteAnonymous;
    }

    /**
     * @param unknown_type $voteAnonymous
     */
    public function setVoteAnonymous($voteAnonymous)
    {
        $this->voteAnonymous = $voteAnonymous;

        return $this;
    }

    /**
     * @return bool
     */
    public function getModerationType()
    {
        return $this->moderationType;
    }

    /**
     * @param bool $moderationType
     */
    public function setModerationType($moderationType)
    {
        $this->moderationType = $moderationType;

        return $this;
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

        if (isset($data['postDisplayMode']) && $data['postDisplayMode'] !== null) {
            $this->postDisplayMode = $data['postDisplayMode'];
        }

        if (isset($data['voteAnonymous']) && $data['voteAnonymous'] !== null) {
            $this->voteAnonymous = $data['voteAnonymous'];
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

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'postDisplayMode',
                        'required' => false,
                        'validators' => array(
                            array(
                                'name' => 'InArray',
                                'options' => array(
                                    'haystack' => array('date', 'vote', 'random'),
                                ),
                            ),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'voteActive',
                        'required' => true,
                        'validators' => array(
                            array(
                                'name' => 'Between',
                                'options' => array('min' => 0, 'max' => 1),
                            ),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'voteAnonymous',
                        'required' => true,
                        'validators' => array(
                            array(
                                'name' => 'Between',
                                'options' => array('min' => 0, 'max' => 1),
                            ),
                        ),
                    )
                )
            );

            $inputFilter->add(
                $factory->createInput(
                    array(
                        'name' => 'moderationType',
                        'required' => false,
                        'validators' => array(
                            array(
                                'name' => 'Between',
                                'options' => array('min' => 0, 'max' => 1),
                            ),
                        ),
                    )
                )
            );

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
