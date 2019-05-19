<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\MemoryController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminMemoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new MemoryController($container);

        return $controller;
    }
}
