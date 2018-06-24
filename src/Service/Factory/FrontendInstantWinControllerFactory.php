<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\InstantWinController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendInstantWinControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new InstantWinController($container);

        return $controller;
    }
}
