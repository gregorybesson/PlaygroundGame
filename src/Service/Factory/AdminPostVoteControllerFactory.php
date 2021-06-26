<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Admin\PostVoteController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class AdminPostVoteControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new PostVoteController($container);

        return $controller;
    }
}
