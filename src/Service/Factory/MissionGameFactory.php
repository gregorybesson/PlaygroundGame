<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\MissionGame;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class MissionGameFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new MissionGame($container);

        return $service;
    }
}
