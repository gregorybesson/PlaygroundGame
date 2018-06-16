<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\QuizController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminQuizControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\QuizController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new QuizController($locator);

        return $controller;
    }
}
