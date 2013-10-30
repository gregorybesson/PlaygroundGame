<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Entry;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;

class InstantWinController extends GameController
{
    /**
     * @var leaderBoardService
     */
    protected $leaderBoardService;

    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }


        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $session = new Container('facebook');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        // Redirect to fan gate if the game require to 'like' the page before playing

        if ($channel == 'facebook' && $session->offsetExists('signed_request')) {
            if($game->getFbFan()){
                if ($sg->checkIsFan($game) === false){
                    return $this->redirect()->toRoute($game->getClassType().'/fangate',array('id' => $game->getIdentifier()));
                }
            }
        }

        if (!$user) {

            // The game is deployed on Facebook, and played from Facebook : retrieve/register user

            if ($channel == 'facebook' && $session->offsetExists('signed_request')) {

                // Get Playground user from Facebook info

                $viewModel = $this->buildView($game);
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch('playgrounduser_user', array('action' => 'registerFacebookUser'));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
                }

            // The game is not played from Facebook : redirect to login/register form

            } else {
                $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
            }

        }

        $viewModel = $this->buildView($game);
        $beforeLayout = $this->layout()->getTemplate();
        // je délègue la responsabilité du formulaire à PlaygroundUser, y compris dans sa gestion des erreurs
        $form = $this->forward()->dispatch('playgrounduser_user', array('action' => 'address'));

        // TODO : suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);
        // Le formulaire est validé, il renvoie true et non un ViewModel
        if (!($form instanceof \Zend\View\Model\ViewModel)) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        if ($this->getRequest()->isPost()) {
            // En post, je reçois la maj du form pour les gagnants. Je n'ai pas à créer une nouvelle participation mais vérifier la précédente
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if (!$lastEntry) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
            }
            $winner = $lastEntry->getWinner();
            // if not winner, I'm not authorized to call this page in POST mode.
            if (!$winner) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
            }

            // si la requete est POST et que j'arrive ici, c'est que le formulaire contient une erreur. Donc je prépare la vue formulaire sans le grattage
            //$viewModel->setTemplate('instant-win/winner/form');
        } else {
            // J'arrive sur le jeu, j'essaie donc de participer
            $entry = $sg->play($game, $user);
            if (!$entry) {
                // the user has already taken part of this game and the participation limit has been reached
                $this->flashMessenger()->addMessage('Vous avez déjà participé');

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }

            // update the winner attribute in entry.
            $winner = $sg->IsInstantWinner($game, $user);
        }

        $viewModel->setVariables(array(
            'game' => $game,
            'winner' => $winner,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));
        $viewModel->addChild($form, 'form');

        return $viewModel;
    }

    public function resultAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user   = $this->zfcUserAuthentication()->getIdentity();
        $sg     = $this->getGameService();

        $statusMail = null;

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $secretKey = strtoupper(substr(sha1($user->getId().'####'.time()),0,15));
        $socialLinkUrl = $this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        if (!$user) {
            $redirect = urlencode($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register') . '?redirect='.$redirect);
        }

        $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
        }

        $winner = $lastEntry->getWinner();

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
            'game'             => $game,
            'winner'           => $winner,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'form'             => $form,
            'socialLinkUrl'    => $socialLinkUrl,
            'secretKey'		   => $secretKey
        ));

        return $viewModel;
    }

    public function fbshareAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$fbId) {
            return false;
        }

        $sg->postFbWall($fbId, $game, $user);

        return true;

    }

    public function tweetAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $tweetId = $this->params()->fromQuery('tweetId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$tweetId) {
            return false;
        }

        $sg->postTwitter($tweetId, $game, $user);

        return true;

    }

    public function googleAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $googleId = $this->params()->fromQuery('googleId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$googleId) {
            return false;
        }

        $sg->postGoogle($googleId, $game, $user);

        return true;

    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_instantwin_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }

    public function getLeaderBoardService()
    {
        if (!$this->leaderBoardService) {
            $this->leaderBoardService = $this->getServiceLocator()->get('playgroundgame_leaderboard_service');
        }

        return $this->leaderBoardService;
    }

    public function setLeaderBoardService(LeaderBoardService $leaderBoardService)
    {
        $this->leaderBoardService = $leaderBoardService;

        return $this;
    }
}
