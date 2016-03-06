<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\LotteryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendLotteryControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\LotteryController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new LotteryController($locator);

        return $controller;
    }
}
