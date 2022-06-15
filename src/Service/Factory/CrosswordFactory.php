<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Crossword;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class CrosswordFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Crossword($container);

        return $service;
    }
}
