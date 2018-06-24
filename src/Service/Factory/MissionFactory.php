<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Mission;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class MissionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Mission($container);

        return $service;
    }
}
