<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\InstantWinController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminInstantWinControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\InstantWinController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new InstantWinController($locator);

        return $controller;
    }
}
