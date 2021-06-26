<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\TradingCardController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendTradingCardControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new TradingCardController($container);

        return $controller;
    }
}
