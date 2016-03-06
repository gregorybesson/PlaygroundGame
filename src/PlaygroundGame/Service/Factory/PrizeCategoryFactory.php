<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\PrizeCategory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrizeCategoryFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\PrizeCategory
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new PrizeCategory($locator);

        return $service;
    }
}
