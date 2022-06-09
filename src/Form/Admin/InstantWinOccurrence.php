<?php

namespace PlaygroundGame\Form\Admin;

use Laminas\Form\Element;
use LmcUser\Form\ProvidesEventsForm;
use Laminas\Mvc\I18n\Translator;
use Laminas\ServiceManager\ServiceManager;

class InstantWinOccurrence extends ProvidesEventsForm
{
    public function __construct($name, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'name' => 'instant_win_id',
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
            'name' => 'value',
            'options' => array(
                'label' => $translator->translate('Value', 'playgroundgame'),
            ),
            'attributes' => array(
                'type' => 'text',
                'id' => 'value',
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
                'label' => $translator->translate('Active', 'playgroundgame'),
            ),
        ));

        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'winning',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
                'label' => $translator->translate('Winning', 'playgroundgame'),
            ),
        ));

        $prizes = $this->getPrizes();
        $this->add(array(
            'type' => 'Laminas\Form\Element\Select',
            'name' => 'prize',
            'options' => array(
                'empty_option' => $translator->translate('Ce jeu n\'a pas de lot associé', 'playgroundgame'),
                'value_options' => $prizes,
                'label' => $translator->translate('Lots', 'playgroundgame'),
            ),
        ));

        $submitElement = new Element\Button('submit');
        $submitElement->setAttributes(array(
            'type'  => 'submit',
            'class' => 'btn btn-primary',
        ));

        $this->add($submitElement, array(
            'priority' => -100,
        ));
    }

    /**
     *
     * @return array
     */
    public function getPrizes()
    {
        $prizes = array();
        $prizeService = $this->getServiceManager()->get('playgroundgame_prize_service');
        $results = $prizeService->getPrizeMapper()->findAll();

        foreach ($results as $result) {
            $prizes[$result->getId()] = $result->getTitle();
        }

        return $prizes;
    }

    /**
     * Retrieve service manager instance
     *
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     *
     * @return InstantWinOccurrence
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
