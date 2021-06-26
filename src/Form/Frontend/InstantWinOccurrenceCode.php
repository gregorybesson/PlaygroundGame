<?php
namespace PlaygroundGame\Form\Frontend;

use ZfcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class InstantWinOccurrenceCode extends ProvidesEventsForm
{
    protected $inputFilter;
    protected $serviceManager;

    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->add(array(
            'type' => 'Laminas\Form\Element\Text',
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
