<?php

namespace PlaygroundGame\Form\Admin;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class InstantWinOccurrenceImport extends ProvidesEventsForm
{
    public function __construct($name = null, ServiceManager $serviceManager, Translator $translator)
    {
        parent::__construct($name);

        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype','multipart/form-data');

        $this->setServiceManager($serviceManager);

        $this->add(array(
            'name' => 'instant_win_id',
            'type'  => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0,
            ),
        ));

        $this->add(array(
            'name' => 'file',
            'type' => 'Zend\Form\Element\File',
            'options' => array(
                'label' => $translator->translate('CSV file containing occurrences', 'playgroundgame'),
            ),
            'attributes' => array(
                'id' => 'file'
            ),
            'validators' => array(
                array('Exists'),
                array('Extension', false, 'csv'),
            ),
        ));

        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'active',
            'options' => array(
                'value_options' => array(
                    '0' => $translator->translate('No', 'playgroundgame'),
                    '1' => $translator->translate('Yes', 'playgroundgame'),
                ),
                'label' => $translator->translate('Active', 'playgroundgame'),
            ),
        ));

        $prizes = $this->getPrizes();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'prize',
            'required' => false,
            'allowEmpty' => true,
            'options' => array(
                'empty_option' => $translator->translate('Ce jeu n\'a pas de lot associé', 'playgroundgame'),
                'value_options' => $prizes,
                'label' => $translator->translate('Lots', 'playgroundgame'),
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

    /**
     *
     * @return array
     */
    public function getPrizes ()
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
    public function getServiceManager ()
    {
        return $this->serviceManager;
    }

    /**
     * Set service manager instance
     *
     * @param  ServiceManager $serviceManager
     * @return User
     */
    public function setServiceManager (ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
