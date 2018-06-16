<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\PostVoteController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FrontendPostVoteControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Frontend\PostVoteController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new PostVoteController($locator);

        return $controller;
    }
}
