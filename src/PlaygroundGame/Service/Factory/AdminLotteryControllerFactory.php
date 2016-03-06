<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\LotteryController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminLotteryControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\LotteryController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new LotteryController($locator);

        return $controller;
    }
}
