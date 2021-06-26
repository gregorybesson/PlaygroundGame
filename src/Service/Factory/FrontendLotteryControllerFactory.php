<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\LotteryController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendLotteryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new LotteryController($container);

        return $controller;
    }
}
