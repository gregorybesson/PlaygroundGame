<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\QuizController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendQuizControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new QuizController($container);

        return $controller;
    }
}
