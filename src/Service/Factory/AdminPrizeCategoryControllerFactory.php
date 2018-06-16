<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\PrizeCategoryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminPrizeCategoryControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\PrizeCategoryController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new PrizeCategoryController($locator);

        return $controller;
    }
}
