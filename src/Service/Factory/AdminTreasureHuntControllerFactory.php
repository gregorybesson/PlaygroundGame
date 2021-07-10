<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\TreasureHuntController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminTreasureHuntControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new TreasureHuntController($container);

        return $controller;
    }
}
