<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\GameController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendGameControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\GameController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new GameController($locator);

        return $controller;
    }
}
