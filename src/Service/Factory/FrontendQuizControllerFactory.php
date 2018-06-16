<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\QuizController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendQuizControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\QuizController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new QuizController($locator);

        return $controller;
    }
}
