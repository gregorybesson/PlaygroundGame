<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\PrizeCategoryUser;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrizeCategoryUserFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\PrizeCategoryUser
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new PrizeCategoryUser($locator);

        return $service;
    }
}
