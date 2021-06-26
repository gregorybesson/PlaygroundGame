<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Controller\Frontend\WebhookController;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FrontendWebhookControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $controller = new WebhookController($container);

        return $controller;
    }
}
