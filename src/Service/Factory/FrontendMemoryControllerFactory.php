<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\MemoryController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendMemoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new MemoryController($container);

        return $controller;
    }
}
