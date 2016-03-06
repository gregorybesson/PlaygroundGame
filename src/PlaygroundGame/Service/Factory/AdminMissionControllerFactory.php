<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\MissionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminMissionControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\MissionController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new MissionController($locator);

        return $controller;
    }
}
