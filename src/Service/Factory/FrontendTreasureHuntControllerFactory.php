<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\TreasureHuntController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendTreasureHuntControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new TreasureHuntController($container);

        return $controller;
    }
}
