<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\TradingCard;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TradingCardFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new TradingCard($container);

        return $service;
    }
}
