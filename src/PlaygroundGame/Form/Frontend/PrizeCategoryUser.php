<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\Mvc\I18n\Translator;
use Zend\ServiceManager\ServiceManager;

class PrizeCategoryUser extends ProvidesEventsForm
{
    protected $serviceManager;

    public function __construct($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);

        $this->add(array(
            'name' => 'user',
            'type' => 'Zend\Form\Element\Hidden',
            'attributes' => array(
                'value' => 0
            )
        ));

        $categories = $this->getPrizeCategories();
        if (count($categories) == 0) {
            $this->add(array(
                    'name' => 'prizeCategory',
                    'type' => 'Zend\Form\Element\Hidden',
                    'attributes' => array(
                        'value' => 0
                    )
            ));
        } else {
            $this->add(array(
                'type' => 'Zend\Form\Element\MultiCheckbox',
                'name' => 'prizeCategory',
                'options' => array(
                    'value_options' => $categories,
                    'label' => $translator->translate('Catégorie de gain', 'playgroundgame')
                ),
                'attributes' => array()
            ));
        }

        $submitElement = new Element\Button('submit');
        $submitElement->setLabel($translator->translate('Create', 'playgroundgame'))
            ->setAttributes(array(
            'type' => 'submit'
        ));

        $this->add($submitElement, array(
            'priority' => - 100
        ));
    }

    /**
     *
     * @return array
     */
    public function getPrizeCategories()
    {
        $prizeCategoryService = $this->getServiceManager()->get('playgroundgame_prizecategory_service');
        $results = $prizeCategoryService->getActivePrizeCategories();

        $categories = array();
        foreach ($results as $result) {
            $categories[$result->getId()] = $result->getTitle();
        }

        return $categories;
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
     * @return PrizeCategoryUser
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
