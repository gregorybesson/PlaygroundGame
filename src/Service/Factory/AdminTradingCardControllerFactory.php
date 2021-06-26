<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\TradingCardController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminTradingCardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new TradingCardController($container);

        return $controller;
    }
}
