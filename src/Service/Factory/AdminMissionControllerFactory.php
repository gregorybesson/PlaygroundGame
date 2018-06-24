<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\MissionController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminMissionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new MissionController($container);

        return $controller;
    }
}
