<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\InstantWinController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminInstantWinControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new InstantWinController($container);

        return $controller;
    }
}
