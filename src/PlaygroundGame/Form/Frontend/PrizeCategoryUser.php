<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Form;
use Zend\Form\Element;
use ZfcBase\Form\ProvidesEventsForm;
use Zend\I18n\Translator\Translator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\ServiceManager\ServiceManager;

class PrizeCategoryUser extends ProvidesEventsForm
{

    protected $serviceManager;

    public function __construct ($name = null, ServiceManager $sm, Translator $translator)
    {
        parent::__construct($name);

        $this->setServiceManager($sm);
        $entityManager = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        // The form will hydrate an object of type "QuizQuestion"
        // This is the secret for working with collections with Doctrine
        // (+ add'Collection'() and remove'Collection'() and "cascade" in
        // corresponding Entity
        // https://github.com/doctrine/DoctrineModule/blob/master/docs/hydrator.md
        //$this->setHydrator(new DoctrineHydrator($entityManager, 'PlaygroundGame\Entity\PrizeCategoryUser'));

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

        /*$this->add(array(
            'name' => 'prizeCategory',
            'type' => 'DoctrineModule\Form\Element\ObjectMultiCheckbox',
            'options' => array(
                'label' => $translator->translate('Catégorie de gain', 'playgroundgame'),
                'object_manager' => $entityManager,
                'target_class' => 'PlaygroundGame\Entity\PrizeCategory',
                'property' => 'title'
            ),
            'attributes' => array(
                'required' => false
            )
        ));*/

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
    public function getPrizeCategories ()
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
