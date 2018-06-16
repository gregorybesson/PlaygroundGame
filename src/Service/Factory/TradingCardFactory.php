<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\TradingCard;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TradingCardFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\TradingCard
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new TradingCard($locator);

        return $service;
    }
}
