<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\GameController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendGameControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new GameController($container);

        return $controller;
    }
}
