<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Service\Mission;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MissionFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\Mission
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Mission($locator);

        return $service;
    }
}
