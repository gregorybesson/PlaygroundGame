<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\PrizeCategoryUser;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PrizeCategoryUserFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new PrizeCategoryUser($container);

        return $service;
    }
}
