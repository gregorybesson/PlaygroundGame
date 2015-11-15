<?php
namespace PlaygroundGame\Form\Frontend;

use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class InstantWinOccurrenceCode extends ProvidesEventsForm
{
    protected $inputFilter;
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'code-input',
            'options' => array(
                'label' => $translator->translate('Entrez votre code'),
                ),
            'attributes' => array(
                'placeholder' => $translator->translate('Code'),
                'allow_empty' => false,
                'required' => true,
                ),
            ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => $translator->translate('Participer'),
            ),
        ));
    }
}
