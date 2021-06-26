<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\GameController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminGameControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new GameController($container);

        return $controller;
    }
}
