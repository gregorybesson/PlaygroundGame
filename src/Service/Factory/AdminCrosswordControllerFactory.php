<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\CrosswordController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminCrosswordControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new CrosswordController($container);

        return $controller;
    }
}
