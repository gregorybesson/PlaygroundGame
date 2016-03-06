<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\InstantWinController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendInstantWinControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\InstantWinController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new InstantWinController($locator);

        return $controller;
    }
}
