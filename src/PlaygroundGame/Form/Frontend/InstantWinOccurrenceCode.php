<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;


class InstantWinOccurrenceCode extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct ($name = null, Translator $translator)
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
                )
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
