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
     * templates
     * @ORM\Column(name="template", type="string", nullable=false)
     */
    protected $template;

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
     * Is it possible to vote anonymously ?
     * @ORM\Column(name="vote_anonymous", type="boolean", nullable=false)
     */
    protected $voteAnonymous;

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
     * @return the unknown_type
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param unknown_type $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return the unknown_type
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

        if (isset($data['template']) && $data['template'] != null) {
            $this->template = $data['template'];
        }

        if (isset($data['postDisplayMode']) && $data['postDisplayMode'] != null) {
            $this->postDisplayMode = $data['postDisplayMode'];
        }

        if (isset($data['voteAnonymous']) && $data['voteAnonymous'] != null) {
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

            $inputFilter->add($factory->createInput(array(
                'name' => 'template',
                'required' => true,
            )));

            $inputFilter
                    ->add(
                            $factory
                                    ->createInput(
                                            array('name' => 'postDisplayMode', 'required' => false,
                                                    'validators' => array(array('name' => 'InArray', 'options' => array('haystack' => array('date', 'vote', 'random'),),),),)));

            $inputFilter->add($factory->createInput(array('name' => 'voteAnonymous', 'required' => true, 'validators' => array(array('name' => 'Between', 'options' => array('min' => 0, 'max' => 1,),),),)));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
