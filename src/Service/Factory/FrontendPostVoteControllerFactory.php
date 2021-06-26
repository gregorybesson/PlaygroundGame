<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\PostVoteController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendPostVoteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new PostVoteController($container);

        return $controller;
    }
}
