<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\TradingCardController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminTradingCardControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\TradingCardController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new TradingCardController($locator);

        return $controller;
    }
}
