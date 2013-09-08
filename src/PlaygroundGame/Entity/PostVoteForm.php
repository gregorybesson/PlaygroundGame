<?php
namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_postvote_form")
 */
class PostVoteForm implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="PostVote", inversedBy="form")
     */
    protected $postvote;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $form;

    /**
     * @ORM\Column(name="form_template", type="text", nullable=true)
     */
    protected $formTemplate;

    public function __construct()
    {
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
        $this->postvote = $postvote;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param unknown_type $title
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param unknown_type $description
     */
    public function setDescription($description)
    {
        $this->description = $description;

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
     * @param unknown_type $value
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return the unknown_type
     */
    public function getFormTemplate()
    {
        return $this->formTemplate;
    }

    /**
     * @param unknown_type $value
     */
    public function setFormTemplate($formTemplate)
    {
        $this->formTemplate = $formTemplate;

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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
