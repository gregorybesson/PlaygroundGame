<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Service\PostVote;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PostVoteFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\PostVote
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new PostVote($locator);

        return $service;
    }
}
