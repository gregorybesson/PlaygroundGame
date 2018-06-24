<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\Quiz;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class QuizFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new Quiz($container);

        return $service;
    }
}
