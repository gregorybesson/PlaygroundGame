<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\TreasureHunt;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TreasureHuntFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new TreasureHunt($container);

        return $service;
    }
}
