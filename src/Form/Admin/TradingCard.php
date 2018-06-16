<?php

namespace PlaygroundGame\Form\Admin;

use PlaygroundCore\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class TradingCard extends Game
{
    public function __construct($name, ServiceManager $sm, Translator $translator)
    {
        $this->setServiceManager($sm);
        $entityManager = $sm->get('doctrine.entitymanager.orm_default');

        // having to fix a Doctrine-module bug :( https://github.com/doctrine/DoctrineModule/issues/180
        $hydrator = new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\TradingCard');
        $hydrator->addStrategy('partner', new \PlaygroundCore\Stdlib\Hydrator\Strategy\ObjectStrategy());
        $this->setHydrator($hydrator);

        parent::__construct($name, $sm, $translator);

        $this->add(array(
            'name' => 'boosterCardNumber',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number of cards in booster', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Number of cards in booster', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'name' => 'boosterDrawQuantity',
            'type' => 'Zend\Form\Element\Text',
            'attributes' => array(
                'placeholder' => $translator->translate('Number of boosters', 'playgroundgame'),
            ),
            'options' => array(
                'label' => $translator->translate('Number of boosters', 'playgroundgame'),
            ),
        ));
    }
}
