<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Prize;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PrizeFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\Prize
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Prize($locator);

        return $service;
    }
}
