<?php
namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\TreasureHunt;
use PlaygroundGame\Controller\Frontend\GameController;
use Laminas\View\Model\ViewModel;
use Laminas\Session\Container;
use PlaygroundGame\Service\GameService;

class TreasureHuntController extends GameController
{

    /**
     * @var gameService
     */
    protected $gameService;
    protected $treasurehunt;

    public function playAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->lmcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);

        $socialLinkUrl = $this->frontendUrl()->fromRoute('treasurehunt', array('id' => $game->getIdentifier()), array('force_canonical' => true));

        $session = new Container('facebook');

        // Redirect to fan gate if the game require to 'like' the page before playing
        if ($session->offsetExists('signed_request')) {
            if($game->getFbFan()){
                if ($sg->checkIsFan($game) === false){
                    return $this->redirect()->toRoute($game->getClassType().'/fangate',array('id' => $game->getIdentifier()));
                }
            }
        }

        if (!$user) {

            // The game is deployed on Facebook, and played from Facebook : retrieve/register user

            if ($session->offsetExists('signed_request')) {

                // Get Playground user from Facebook info
                $viewModel = $this->buildView($game);
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user','action' => 'registerFacebookUser', 'provider' => $channel));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirect = urlencode($this->frontendUrl()->fromRoute('treasurehunt/play', array('id' => $game->getIdentifier()), array('force_canonical' => true)));
                    return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('lmcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
                }

                // The game is not played from Facebook : redirect to login/register form

            } elseif(!$game->getAnonymousAllowed()) {
                $redirect = urlencode($this->frontendUrl()->fromRoute('treasurehunt/play', array('id' => $game->getIdentifier()), array('force_canonical' => true)));
                return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('lmcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
            }

        }


        $entry = $sg->play($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé');

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('treasurehunt/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        if ($this->getRequest()->isPost()) {
            $response = $this->getResponse();
            $data = $this->getRequest()->getPost()->toArray();

            $entry = $this->getGameService()->analyzeClue($game, $data, $user);

            if($entry->getActive()){
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => $entry->getWinner(),
                'url' => $this->frontendUrl()->fromRoute('' . $game->getClassType().'/play', array('id' => $game->getIdentifier()), array('force_canonical' => true))
            )));
            } else{
                $response->setContent(\Laminas\Json\Json::encode(array(
                    'success' => $entry->getWinner(),
                    'url' => $this->frontendUrl()->fromRoute('' . $game->getClassType().'/'.$game->nextStep($this->params('action')), array('id' => $game->getIdentifier()), array('force_canonical' => true))
                )));
            }

            return $response;
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
                'game' => $game,
                'flashMessages' => $this->flashMessenger()->getMessages(),
                'step' => $entry->getStep()
            )
        );

        return $viewModel;
    }

    public function resultAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $statusMail = null;

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute('treasurehunt', array('id' => $this->game->getIdentifier()), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user, $this->params()->fromQuery('anonymous_identifier'));
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('treasurehunt', array('id' => $this->game->getIdentifier()), array('force_canonical' => true)));
        }

        if (!$this->user && !$this->game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('treasurehunt/result', array('id' => $this->game->getIdentifier())));
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('lmcuser/register') . '?redirect='.$redirect);
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                    //$bonusEntry = $sg->addAnotherChance($this->game, $this->user, 1);
                }
            }
        }

        // buildView must be before sendMail because it adds the game template path to the templateStack
        // TODO : Improve this.
        $viewModel = $this->buildView($this->game);

        $this->getGameService()->sendMail($this->game, $this->user, $lastEntry);
        $missionService = $this->getServiceLocator()->get('playgroundgame_mission_game_service');
        $nextGame = $missionService->checkCondition($this->game, $lastEntry->getWinner(), true, $lastEntry);

        $viewModel->setVariables(array(
                'statusMail'       => $statusMail,
                'game'             => $this->game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
                'form'             => $form,
                'socialLinkUrl'    => $socialLinkUrl,
                'secretKey'		   => $secretKey,
                'nextGame'         => $nextGame,
            )
        );

        return $viewModel;
    }

    public function fbshareAction()
    {
         $sg = $this->getGameService();
         $result = parent::fbshareAction();
         $bonusEntry = false;

         if ($result) {
             $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
             $user = $this->lmcUserAuthentication()->getIdentity();
             $game = $sg->checkGame($identifier);
             $bonusEntry = $sg->addAnotherChance($game, $user, 1);
         }

         $response = $this->getResponse();
         $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
         )));

         return $response;
    }

    public function fbrequestAction()
    {
        $sg = $this->getGameService();
        $result = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->lmcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function tweetAction()
    {
        $sg = $this->getGameService();
        $result = parent::tweetAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->lmcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function googleAction()
    {
        $sg = $this->getGameService();
        $result = parent::googleAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->lmcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_treasurehunt_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }
}
