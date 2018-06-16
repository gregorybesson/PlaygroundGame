<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\GameController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminGameControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\GameController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new GameController($locator);

        return $controller;
    }
}
