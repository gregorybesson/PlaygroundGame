<?php

namespace PlaygroundGame\Service;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\ServiceManager\ServiceManager;
use ZfcBase\EventManager\EventProvider;
use PlaygroundGame\Options\ModuleOptions;
use PlaygroundGame\Mapper\PrizeCategory as PrizeCategoryMapper;
use Zend\Stdlib\ErrorHandler;

class PrizeCategory extends EventProvider implements ServiceManagerAwareInterface
{

    /**
     * @var prizeCategoryMapper
     */
    protected $prizeCategoryMapper;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     *
     * This service is ready for all types of games
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function create(array $data, $prizeCategory, $formClass)
    {

        $form  = $this->getServiceManager()->get($formClass);
        $form->bind($prizeCategory);

        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        $prizeCategory = $this->getPrizeCategoryMapper()->insert($prizeCategory);

        if (!empty($data['upload_picto']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['upload_picto']['tmp_name'], $path . $prizeCategory->getId() . "-" . $data['upload_picto']['name']);
            $prizeCategory->setPicto($media_url . $prizeCategory->getId() . "-" . $data['upload_picto']['name']);
            ErrorHandler::stop(true);
        }

        $prizeCategory = $this->getPrizeCategoryMapper()->update($prizeCategory);

        return $prizeCategory;
    }

    /**
     *
     * @param  array                  $data
     * @param  string                 $entityClass
     * @param  string                 $formClass
     * @return \PlaygroundGame\Entity\Game
     */
    public function edit(array $data, $prizeCategory, $formClass)
    {

        $form  = $this->getServiceManager()->get($formClass);
        $form->bind($prizeCategory);

        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        if (!empty($data['upload_picto']['tmp_name'])) {
            ErrorHandler::start();
            move_uploaded_file($data['upload_picto']['tmp_name'], $path . $prizeCategory->getId() . "-" . $data['upload_picto']['name']);
            $prizeCategory->setPicto($media_url . $prizeCategory->getId() . "-" . $data['upload_picto']['name']);
            ErrorHandler::stop(true);
        }

        $prizeCategory = $this->getPrizeCategoryMapper()->update($prizeCategory);

        return $prizeCategory;
    }

    public function getActivePrizeCategories()
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $query = $em->createQuery('SELECT p FROM PlaygroundGame\Entity\PrizeCategory p WHERE p.active = true');
        $categories = $query->getResult();

        return $categories;
    }

    /**
     * getPrizeCategoryMapper
     *
     * @return PrizeCategoryMapper
     */
    public function getPrizeCategoryMapper()
    {
        if (null === $this->prizeCategoryMapper) {
            $this->prizeCategoryMapper = $this->getServiceManager()->get('playgroundgame_prizecategory_mapper');
        }

        return $this->prizeCategoryMapper;
    }

    /**
     * setPrizeCategoryMapper
     *
     * @param  PrizeCategoryMapper $prizeCategoryMapper
     * @return User
     */
    public function setPrizeCategoryMapper(PrizeCategoryMapper $prizeCategoryMapper)
    {
        $this->prizeCategoryMapper = $prizeCategoryMapper;

        return $this;
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceManager()->get('playgroundgame_module_options'));
        }

        return $this->options;
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
     * @return User
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }
}
