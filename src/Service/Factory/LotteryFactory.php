<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Lottery;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LotteryFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\Lottery
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Lottery($locator);

        return $service;
    }
}
