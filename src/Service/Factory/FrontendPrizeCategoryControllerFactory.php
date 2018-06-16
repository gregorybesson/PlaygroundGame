<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\PrizeCategoryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendPrizeCategoryControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\PrizeCategoryController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new PrizeCategoryController($locator);

        return $controller;
    }
}
