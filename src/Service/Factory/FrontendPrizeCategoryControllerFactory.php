<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\PrizeCategoryController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendPrizeCategoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new PrizeCategoryController($container);

        return $controller;
    }
}
