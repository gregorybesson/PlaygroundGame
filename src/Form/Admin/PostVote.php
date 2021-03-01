<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class PostVote extends Game
{
  public function __construct($name, ServiceManager $sm, Translator $translator)
  {
    $this->setServiceManager($sm);
    $entityManager = $sm->get('doctrine.entitymanager.orm_default');

    // Mapping of an Entity to get value by getId()... Should be taken in charge by Doctrine Hydrator Strategy...
    // having to fix a DoctrineModule bug :( https://github.com/doctrine/DoctrineModule/issues/180
    $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\PostVote');
    $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
    $this->setHydrator($hydrator);

    parent::__construct($name, $sm, $translator);

    $this->add([
      'type' => 'Zend\Form\Element\Select',
      'name' => 'postDisplayMode',
      'attributes' => [
        'id' => 'postDisplayMode',
        'options' => [
          'date' => $translator->translate('Date', 'playgroundgame'),
          'vote' => $translator->translate('Vote number', 'playgroundgame'),
          'random' => $translator->translate('Random', 'playgroundgame'),
        ],
      ],
      'options' => [
        'empty_option' => $translator->translate('Display posts orber by', 'playgroundgame'),
        'label' => $translator->translate('Posts display mode', 'playgroundgame'),
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Text',
      'name' => 'postDisplayNumber',
      'attributes' => [
        'id' => 'postDisplayNumber',
      ],
      'options' => [
        'empty_option' => $translator->translate('Number of displayed posts', 'playgroundgame'),
        'label' => $translator->translate('Number of displayed posts', 'playgroundgame'),
      ],
    ]);

    $this->add(
      [
        'type' => 'Zend\Form\Element\Checkbox',
        'name' => 'voteActive',
        'attributes' => [
          'class' => 'switch-input',
          'value' => 1,
        ],
        'options' => [
          'label' => $translator->translate('Allow votes on this game', 'playgroundgame'),
          'checked_value' => 1,
          'unchecked_value' => 0,
        ],
      ]
    );

    $this->add([
      'type' => 'Zend\Form\Element\Checkbox',
      'name' => 'voteAnonymous',
      'attributes' => [
        'class' => 'switch-input',
      ],
      'options' => [
        'label' => $translator->translate('Allow anonymous visitors to vote', 'playgroundgame'),
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Select',
      'name' => 'moderationType',
      'attributes' => [
        'id' => 'moderationType',
        'options' => [
          '0' => $translator->translate('Post moderation', 'playgroundgame'),
          '1' => $translator->translate('Pre moderation', 'playgroundgame'),
        ],
      ],
      'options' => [
        'empty_option' => $translator->translate('Moderation type', 'playgroundgame'),
        'label' => $translator->translate('Moderation type', 'playgroundgame'),
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Checkbox',
      'name' => 'mailModerationValidated',
      'attributes' => [
        'class' => 'switch-input',
      ],
      'options' => [
        'label' => $translator->translate('Send a mail to the player when the post is validated', 'playgroundgame'),
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Textarea',
      'name' => 'mailModerationValidatedBlock',
      'options' => [
        'label' => $translator->translate('Validated moderation mail\'s content', 'playgroundgame'),
      ],
      'attributes' => [
        'cols' => '10',
        'rows' => '10',
        'id' => 'mailModerationValidatedBlock',
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Checkbox',
      'name' => 'mailModerationRejected',
      'attributes' => [
        'class' => 'switch-input',
      ],
      'options' => [
        'label' => $translator->translate('Send a mail to the player when the post is validated', 'playgroundgame'),
      ],
    ]);

    $this->add([
      'type' => 'Zend\Form\Element\Textarea',
      'name' => 'mailModerationRejectedBlock',
      'options' => [
        'label' => $translator->translate('Validated moderation mail\'s content', 'playgroundgame'),
      ],
      'attributes' => [
        'cols' => '10',
        'rows' => '10',
        'id' => 'mailModerationRejectedBlock',
      ],
    ]);
  }
}
