<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Memory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class MemoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Memory($container);

        return $service;
    }
}
