<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\MissionController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendMissionControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\MissionController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new MissionController($locator);

        return $controller;
    }
}
