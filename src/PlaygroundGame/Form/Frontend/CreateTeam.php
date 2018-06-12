<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class CreateTeam extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->add(array(
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('Team name', 'playgroundgame').' 1',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Team name', 'playgroundgame').' 1',
                'class' => 'large-input',
                'autocomplete' => 'off'
            )
        ));
        
        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Send', 'playgroundgame'))
            ->setAttributes(array(
            'type' => 'submit',
            'class'=> 'btn btn-warning'
            ));

        $this->add($submitElement, array(
            'priority' => - 100
        ));
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return ShareMail
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
