<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;

class WebhookController extends AbstractActionController
{
    protected $gameService;

    protected $prizeCategoryService;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
    
    /**
     * FB hooks : hub.mode=subscribe&
     * hub.challenge=1158201444&
     * hub.verify_token=meatyhamhock
     */
    public function indexAction()
    {

        $mode = $this->params()->fromQuery('hub_mode');
        $challenge = $this->params()->fromQuery('hub_challenge');
        $token = $this->params()->fromQuery('hub_verify_token');

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode($token));

        return $response;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_game_service');
        }

        return $this->gameService;
    }

    public function getPrizeCategoryService()
    {
        if (!$this->prizeCategoryService) {
            $this->prizeCategoryService = $this->getServiceLocator()->get('playgroundgame_prizecategory_service');
        }

        return $this->prizeCategoryService;
    }

    public function setPrizeCategoryService(\PlaygroundGame\Service\PrizeCategory $prizeCategoryService)
    {
        $this->prizeCategoryService = $prizeCategoryService;

        return $this;
    }
}
