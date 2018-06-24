<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\InstantWin;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class InstantWinFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new InstantWin($container);

        return $service;
    }
}
