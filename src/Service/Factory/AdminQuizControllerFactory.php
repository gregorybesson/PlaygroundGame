<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\QuizController;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminQuizControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new QuizController($container);

        return $controller;
    }
}
