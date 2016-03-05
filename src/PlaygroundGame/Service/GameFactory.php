<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Service\Game;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GameFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\Game
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Game($locator);

        return $service;
    }
}
