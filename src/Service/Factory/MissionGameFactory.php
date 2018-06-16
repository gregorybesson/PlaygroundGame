<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\MissionGame;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MissionGameFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\MissionGame
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new MissionGame($locator);

        return $service;
    }
}
