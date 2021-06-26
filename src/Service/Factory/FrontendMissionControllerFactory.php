<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\MissionController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendMissionControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new MissionController($container);

        return $controller;
    }
}
