<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use Zend\ServiceManager\ServiceManager;

class Prize extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        parent::__construct();
        $this->setAttribute('enctype','multipart/form-data');

        $this->add(array(
            'name' => 'id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'title',
            'options' => array(
                'label' => $translator->translate('Title', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'placeholder' => $translator->translate('Title', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'identifier',
            'options' => array(
                'label' => $translator->translate('Slug', 'playgroundgame')
            ),
            'attributes' => array(
                'type' => 'text'
            )
        ));

        $this->add(array(
        	'type' => 'Zend\Form\Element\Textarea',
        	'name' => 'content',
       		'options' => array(
       			'label' => $translator->translate('Description', 'playgroundgame')
       		),
       		'attributes' => array(
       			'cols' => '10',
       			'rows' => '10',
       			'id' => 'content'
       		)
        ));
        
        $this->add(array(
        	'name' => 'qty',
        	'options' => array(
       			'label' => $translator->translate('Quantity', 'playgroundgame')
       		),
       		'attributes' => array(
       			'type' => 'text',
       			'placeholder' => $translator->translate('Quantity', 'playgroundgame')
       		)
        ));
        
        $this->add(array(
        	'name' => 'unitPrice',
        	'options' => array(
       			'label' => $translator->translate('Prix', 'playgroundgame')
       		),
       		'attributes' => array(
       			'type' => 'text',
       			'placeholder' => $translator->translate('Prix', 'playgroundgame')
       		)
        ));
        
        $this->add(array(
       		'type' => 'Zend\Form\Element\Select',
       		'name' => 'currency',
       		'attributes' =>  array(
        		'id' => 'currency',
        		'options' => array(
        			'EU' => $translator->translate('Euro', 'playgroundgame'),
       				'DO' => $translator->translate('Dollar', 'playgroundgame'),
   				),
       		),
       		'options' => array(
        		'empty_option' => $translator->translate('Afficher les Posts triés par', 'playgroundgame'),
    			'label' => $translator->translate('Mode d\'affichage des Posts', 'playgroundgame'),
       		),
        ));
         
        $this->add(array(
          'name' => 'picture_file',
          'options' => array(
            'label' => $translator->translate('Picture', 'playgroundgame')
          ),
          'attributes' => array(
            'type' => 'file',
          )
        ));
        $this->add(array(
                'name' => 'picture',
                'type' => 'Zend\Form\Element\Hidden',
                'attributes' => array(
                    'value' => ''
                )
        ));
               
        $submitElement = new Element\Button('submit');
        $submitElement
        ->setAttributes(array(
            'type'  => 'submit',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));

    }
}
