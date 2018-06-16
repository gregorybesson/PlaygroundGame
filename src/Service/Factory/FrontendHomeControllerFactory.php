<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\HomeController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendHomeControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\HomeController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new HomeController($locator);

        return $controller;
    }
}
