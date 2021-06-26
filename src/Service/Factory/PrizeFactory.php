<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Prize;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PrizeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Prize($container);

        return $service;
    }
}
