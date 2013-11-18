<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundGame\Entity\QuizAnswer;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class QuizAnswerFieldset extends Fieldset
{
    public function __construct($name = null,ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);
        $entityManager = $serviceManager->get('doctrine.entitymanager.orm_default');

        $this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\QuizAnswer'))
        ->setObject(new QuizAnswer());

        $this->add(array(
            'type' => 'Zend\Form\Element\Hidden',
            'name' => 'id'
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
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
                'type' => 'Zend\Form\Element\Select',
                'name' => 'correct',
                'options' => array(
                        //'empty_option' => $translator->translate('Is the answer correct ?', 'playgroundgame'),
                        'value_options' => array(
                            '0' => $translator->translate('Non', 'playgroundgame'),
                            '1' => $translator->translate('Oui', 'playgroundgame'),
                        ),
                        'label' => $translator->translate('Bonne réponse', 'playgroundgame'),
                ),
        ));

        $this->add(array(
            'name' => 'position',
            'options' => array(
                'label' => 'Position'
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Textarea',
            'name' => 'explanation',
            'options' => array(
                'label' => $translator->translate('Explanation', 'playgroundgame'),
            ),
            'attributes' => array(
                'cols' => '10',
                'rows' => '10',
                'id' => 'explanation'
            )
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Button',
            'name' => 'remove',
            'options' => array(
                'label' => $translator->translate('Remove', 'playgroundgame'),
            ),
            'attributes' => array(
                'class' => 'delete-button',
            )
        ));

/*        $this->add(array(
                //'type' => 'Zend\Form\Element\Hidden',
                'name' => 'video',
                'options' => array(
                        'label' => 'Video'
                ),
        ));

        $this->add(array(
                //'type' => 'Zend\Form\Element\Hidden',
                'name' => 'image',
                'options' => array(
                        'label' => 'Image'
                ),
        ));
*/
/*        $this->add(array(
                //'type' => 'Zend\Form\Element\Hidden',
                'name' => 'points',
                'options' => array(
                    'label' => 'Points',
                    'class' => 'input-mini'
                ),
                'attributes' =>  array(
                    'class' => 'input-mini'
                ),
        ));
*/

    }
}
