<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\PostVoteController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AdminPostVoteControllerFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Controller\Admin\PostVoteController
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $controller = new PostVoteController($locator);

        return $controller;
    }
}
