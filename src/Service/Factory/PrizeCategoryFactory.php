<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\PrizeCategory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PrizeCategoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new PrizeCategory($container);

        return $service;
    }
}
