<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\TradingCardController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendTradingCardControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\TradingCardController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new TradingCardController($locator);

        return $controller;
    }
}
