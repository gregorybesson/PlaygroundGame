<?php
namespace PlaygroundGame\Form\Frontend;

use ZfcUser\Options\RegistrationOptionsInterface;
use Zend\Mvc\I18n\Translator;

class Register extends \PlaygroundUser\Form\Register
{
    /**
     *
     * @var RegistrationOptionsInterface
     */
    protected $createOptionsOptions;

    protected $serviceManager;

    public function __construct(
        $name,
        RegistrationOptionsInterface $registerOptions,
        Translator $translator,
        $serviceManager
    ) {
    
        $this->setServiceManager($serviceManager);
        parent::__construct($name, $registerOptions, $translator, $serviceManager);

        $this->get('optin')
            ->setAttributes(array('type' => 'checkbox'));

        $this->get('optinPartner')
            ->setAttributes(array('type' => 'checkbox'));

        $this->add(array(
            'name' => 'address',
            'options' => array(
                'label' => $translator->translate('Address', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'large-input required',
                'placeholder' => $translator->translate('Address', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'address2',
            'options' => array(
                'label' => $translator->translate('Address2', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'large-input required',
                'placeholder' => $translator->translate('Address2', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'city',
            'options' => array(
                'label' => $translator->translate('City', 'playgrounduser')
            ),
            'attributes' => array(
                'type' => 'text',
                'class'=> 'large-input required',
                'placeholder' => $translator->translate('City', 'playgrounduser')
            )
        ));

        $this->add(array(
            'name' => 'telephone',
            'options' => array(
                'label' => $translator->translate('Telephone', 'playgrounduser')
            ),
            'attributes' => array(
                'id' => 'telephone',
                'type' => 'text',
                'class'=> 'medium-input required number',
                'minlength' => 5,
                'maxlength' => 10,
                'placeholder' => $translator->translate('Telephone', 'playgrounduser')
            )
        ));
    }

    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }
}
