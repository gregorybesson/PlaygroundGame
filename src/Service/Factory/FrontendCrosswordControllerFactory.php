<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\CrosswordController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendCrosswordControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new CrosswordController($container);

        return $controller;
    }
}
