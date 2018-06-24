<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\HomeController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendHomeControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new HomeController($container);

        return $controller;
    }
}
