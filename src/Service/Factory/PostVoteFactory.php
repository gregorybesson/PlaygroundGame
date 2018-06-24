<?php
namespace PlaygroundGame\Service\Factory;

use PlaygroundGame\Service\PostVote;
use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class PostVoteFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        $service = new PostVote($container);

        return $service;
    }
}
