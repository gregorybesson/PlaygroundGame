<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\PrizeCategoryController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminPrizeCategoryControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new PrizeCategoryController($container);

        return $controller;
    }
}
