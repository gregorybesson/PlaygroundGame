<?php
namespace PlaygroundGame\Service;

use PlaygroundGame\Service\InstantWin;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InstantWinFactory implements FactoryInterface
{
    /**
    * @param ServiceLocatorInterface $locator
    * @return \PlaygroundGame\Service\InstantWin
    */
    public function createService(ServiceLocatorInterface $locator)
    {
        $service = new InstantWin($locator);

        return $service;
    }
}
