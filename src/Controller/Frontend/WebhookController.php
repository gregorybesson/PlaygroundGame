<?php

namespace PlaygroundGame\Controller\Frontend;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\ServiceManager\ServiceLocatorInterface;

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

    public function indexAction()
    {

        $mode = $this->params()->fromQuery('hub_mode');
        $challenge = $this->params()->fromQuery('hub_challenge');
        $token = $this->params()->fromQuery('hub_verify_token');

        $response = $this->getResponse();
        $response->setContent($challenge);

        file_get_contents('php://input');

        return $response;
    }

    /**
     * FB hooks : hub.mode=subscribe&
     * hub.challenge=1158201444&
     * hub.verify_token=meatyhamhock
     */
    public function facebookAction()
    {

        error_log("APPEL RECU");
        if ($this->getRequest()->isPost()) {
            $in = file_get_contents('php://input');
            error_log($in);
            $in = json_decode($in);
            error_log($in->entry[0]->changes[0]->field);
            error_log($in->entry[0]->changes[0]->value->post_id);
        }

        $mode = $this->params()->fromQuery('hub_mode');
        $challenge = $this->params()->fromQuery('hub_challenge');
        $token = $this->params()->fromQuery('hub_verify_token');

        $response = $this->getResponse();
        $response->setContent($challenge);

        file_get_contents('php://input');

        return $response;
    }

    public function instagramAction()
    {

        $mode = $this->params()->fromQuery('hub_mode');
        $challenge = $this->params()->fromQuery('hub_challenge');
        $token = $this->params()->fromQuery('hub_verify_token');

        $response = $this->getResponse();
        $response->setContent($challenge);

        file_get_contents('php://input');

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
