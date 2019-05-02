<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Element;
use ZfcUser\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class ShareMail extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->add(array(
            'name' => 'email[1]',
            'options' => array(
                'label' => $translator->translate('Email address', 'playgroundgame').' 1',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Email address', 'playgroundgame').' 1',
                'class' => 'large-input',
                'autocomplete' => 'off',
            ),
        ));

        $this->add(array(
            'name' => 'email[2]',
            'options' => array(
                'label' => $translator->translate('Email address', 'playgroundgame').' 2',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Email address', 'playgroundgame').' 2',
                'class' => 'large-input',
                'autocomplete' => 'off',
            ),
        ));

        $this->add(array(
            'name' => 'email[3]',
            'options' => array(
                'label' => $translator->translate('Email address', 'playgroundgame').' 3',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Email address', 'playgroundgame').' 3',
                'class' => 'large-input',
                'autocomplete' => 'off',
            ),
        ));

        $this->add(array(
            'name' => 'email[4]',
            'options' => array(
                'label' => $translator->translate('Email address', 'playgroundgame').' 4',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Email address', 'playgroundgame').' 4',
                'class' => 'large-input',
                'autocomplete' => 'off',
            ),
        ));
        
        $this->add(array(
            'name' => 'email[5]',
            'options' => array(
                'label' => $translator->translate('Email address', 'playgroundgame').' 5',
            ),
            'attributes' => array(
                'type' => 'email',
                'placeholder' => $translator->translate('Email address', 'playgroundgame').' 5',
                'class' => 'large-input',
                'autocomplete' => 'off',
            ),
        ));
        
        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Send', 'playgroundgame'))
            ->setAttributes(array(
                'type' => 'submit',
                'class'=> 'btn btn-warning',
            ));

        $this->add($submitElement, array(
            'priority' => - 100,
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
     *
     * @return ShareMail
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
