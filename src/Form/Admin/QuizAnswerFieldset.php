<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Entity\QuizAnswer;
use Laminas\Form\Fieldset;
use Laminas\Mvc\I18n\Translator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Laminas\ServiceManager\ServiceManager;

class QuizAnswerFieldset extends Fieldset
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\QuizAnswer'))
        ->setObject(new QuizAnswer());

        $this->add(array(
            'type' => 'Laminas\Form\Element\Hidden',
            'name' => 'id',
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'answer',
            'options' => array(
                'label' => $translator->translate('Réponse', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '2',
                'id' => 'answer',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'correct',
            'options' => array(
                    'value_options' => array(
                        '0' => $translator->translate('Non', 'playgroundgame'),
                        '1' => $translator->translate('Oui', 'playgroundgame'),
                    ),
                    'label' => $translator->translate('Bonne réponse', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'jsonData',
            'options' => array(
                'label' => $translator->translate('json Data', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '60',
                'rows' => '4',
                'id' => 'jsonData',
            ),
        ));

        $this->add(array(
            'name' => 'points',
            'options' => array(
                'label' => 'Points',
            ),
        ));

        $this->add(array(
            'name' => 'position',
            'options' => array(
                'label' => 'Position',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'explanation',
            'options' => array(
                'label' => $translator->translate('Explanation', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'explanation',
            ),
        ));
        
        $this->add(array(
            'type' => 'Laminas\Form\Element\File',
            'name' => 'upload_image',
            'options' => array(
                'label' => $translator->translate('Image', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
            //                 'id' => 'image',
            //                 'multiple' => true
            ),
        ));
        $this->add(array(
            'name' => 'image',
            'type'  => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
            ),
        ));
        $this->add(array(
            'name' => 'delete_image',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => '',
                'class' => 'delete_image',
            ),
        ));

        $this->add(array(
            'name' => 'video',
            'options' => array(
                'label' => $translator->translate('Video URL', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Button',
            'name' => 'remove',
            'options' => array(
                'label' => $translator->translate('Remove this answer', 'playgroundgame'),
            ),
            'attributes' => array(
                'class' => 'btn btn-block btn-danger col-md-2 delete-button',
            ),
        ));
    }
}
