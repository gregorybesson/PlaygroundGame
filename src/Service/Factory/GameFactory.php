<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Game;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class GameFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Game($container);

        return $service;
    }
}
