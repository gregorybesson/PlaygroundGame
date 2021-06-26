<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Lottery;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class LotteryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Lottery($container);

        return $service;
    }
}
