<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\LotteryController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminLotteryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new LotteryController($container);

        return $controller;
    }
}
