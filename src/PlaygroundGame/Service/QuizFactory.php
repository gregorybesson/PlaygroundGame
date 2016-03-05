<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Service\Quiz;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class QuizFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\Quiz
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new Quiz($locator);

        return $service;
    }
}
