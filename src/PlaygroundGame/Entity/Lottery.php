<?php
namespace PlaygroundGame\Entity;

use PlaygroundGame\Entity\Game;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity @HasLifecycleCallbacks
 * @ORM\Table(name="game_lottery")
 */
class Lottery extends Game implements InputFilterAwareInterface
{
    const CLASSTYPE = 'lottery';
    /**
     * Automatic Draw
     * @ORM\Column(name="draw_auto", type="boolean", nullable=false)
     */
    protected $drawAuto = 0;

    /**
     * @ORM\Column(name="draw_date", type="datetime", nullable=true)
     */
    protected $drawDate;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $winners;

    /**
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $substitutes;

    public function __construct()
    {
    	parent::__construct();
        $this->setClassType(self::CLASSTYPE);
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
     * @return \DateTime
     */
    public function getDrawDate()
    {
        /*if ($this->drawDate) {
            return $this->drawDate->format('d/m/Y');
        } else {
            return null;
        }*/

        return $this->drawDate;
    }

    /**
     * @param \DateTime $drawDate
     */
    public function setDrawDate($drawDate)
    {
        $this->drawDate = $drawDate;

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

        if (isset($obj_vars['drawDate']) && $obj_vars['drawDate'] != null) {
            $obj_vars['drawDate'] = $obj_vars['drawDate']->format('d/m/Y');
        }

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

        if (isset($data['drawAuto']) && $data['drawAuto'] != null) {
            $this->drawAuto = $data['drawAuto'];
        }
        $this->drawDate = (isset($data['drawDate']) && $data['drawDate'] != null) ? \DateTime::createFromFormat('d/m/Y', $data['drawDate']) : null;
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
                'name' => 'drawDate',
                'required' => false
            )));
            /*$inputFilter->add($factory->createInput(array(
                    'name'       => 'id',
                    'required'   => true,
                    'filters' => array(
                        array('name'    => 'Int'),
                    ),
            )));*/

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
