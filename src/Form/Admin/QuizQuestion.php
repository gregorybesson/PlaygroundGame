<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use PlaygroundCore\Stdlib\Hydrator\Strategy\BooleanStrategy;
use Laminas\ServiceManager\ServiceManager;

class QuizQuestion extends ProvidesEventsForm
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object of type "QuizQuestion"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\QuizQuestion');
        $hydrator->addStrategy('autoplay', new BooleanStrategy());
        $hydrator->addStrategy('timer', new BooleanStrategy());
        $this->setHydrator($hydrator);


        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(array(
            'name' => 'quiz_id',
            'type'  => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'id',
            'type'  => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
                'type' => 'Laminas\Form\Element\Textarea',
                'name' => 'question',
                'options' => array(
                    'label' => $translator->translate('Question', 'playgroundgame'),
                    'label_attributes' => array(
                        'class' => 'control-label',
                    ),
                ),
                'attributes' => array(
                    'required' => false,
                    'cols' => '10',
                    'rows' => '2',
                    'id' => 'question',
                ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'hint',
            'options' => array(
                'label' => $translator->translate('Indice', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
            'attributes' => array(
                'required' => false,
                'cols' => '10',
                'rows' => '2',
                'id' => 'question',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Textarea',
            'name' => 'jsonData',
            'options' => array(
                'label' => $translator->translate('Json Data', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
            'attributes' => array(
                'required' => false,
                'cols' => '40',
                'rows' => '10',
                'id' => 'jsonData',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Checkbox',
            'name' => 'prediction',
            'options' => array(
                'label' => 'Question pronostic',
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'timer',
            'type'  => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'timer_duration',
            'options' => array(
                'label' => $translator->translate('Durée du chrono', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'type' => 'Laminas\Form\Element\Radio',
            'options' => array(
                'label' => $translator->translate('Type', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
                'value_options' => array(
                    '0' => $translator->translate('Single response', 'playgroundgame'),
                    '1' => $translator->translate('Multiple choice', 'playgroundgame'),
                    '2' => $translator->translate('Input field', 'playgroundgame'),
                    '3' => $translator->translate('Drag & Drop', 'playgroundgame'),
                ),
            ),
        ));

        $this->add(array(
            'type'  => 'Laminas\Form\Element\Hidden',
            'name' => 'weight',
            'options' => array(
                'label' => $translator->translate('Weight', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'position',
            'options' => array(
                'label' => $translator->translate('Position', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'video',
            'options' => array(
                'label' => $translator->translate('Video URL', 'playgroundgame'),
                'label_attributes' => array(
                    'class' => 'control-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'audio',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'autoplay',
            'type' => 'Laminas\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        // Adding an empty upload field to be able to correctly handle this on the service side.
        $this->add(array(
          'name' => 'upload_image',
          'attributes' => array(
            'type'  => 'file',
          ),
          'options' => array(
            'label' => $translator->translate('Image', 'playgroundgame'),
            'label_attributes' => array(
              'class' => 'control-label',
            ),
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

        $quizAnswerFieldset = new QuizAnswerFieldset(null, $serviceManager, $translator);
        $this->add(array(
          'type'    => 'Laminas\Form\Element\Collection',
          'name'    => 'answers',
          'options' => array(
            'id'    => 'answers',
            'label' => $translator->translate('List of answers', 'playgroundgame'),
            'count' => 0,
            'should_create_template' => true,
            'allow_add' => true,
            'allow_remove' => true,
            'target_element' => $quizAnswerFieldset,
          ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement
        ->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }
}
