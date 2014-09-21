<?php

namespace PlaygroundGame\Controller\Frontend;

class LotteryController extends GameController
{

    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {
        $sg         = $this->getGameService();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $game = $sg->checkGame($identifier);
        if (! $game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $game, $channel);
        if($redirectFb){
            return $redirectFb;
        }

        $user       = $this->zfcUserAuthentication()->getIdentity();
        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute(''. $game->getClassType() . '/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $entry = $sg->play($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé');

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('lottery/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        // Every entry is eligible to draw
        $entry->setDrawable(true);
        $entry->setActive(false);
        $sg->getEntryMapper()->update($entry);

        return $this->redirect()->toUrl($this->frontendUrl()->fromRoute(''. $game->getClassType() . '/'. $game->nextStep($this->params('action')), array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
    }

    public function resultAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $statusMail = null;

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute('lottery', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $sg->findLastInactiveEntry($game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('lottery', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
        }

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('lottery/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                    $bonusEntry = $sg->addAnotherChance($game, $user, 1);
                }
            }
        }

        // buildView must be before sendMail because it adds the game template path to the templateStack
        // TODO : Improve this.
        $viewModel = $this->buildView($game);
        
        $this->sendMail($game, $user, $lastEntry);

        $nextGame = parent::getMissionGameService()->checkCondition($game, $lastEntry->getWinner(), true, $lastEntry);

        $viewModel->setVariables(array(
                'statusMail'       => $statusMail,
                'game'             => $game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
                'form'             => $form,
                'socialLinkUrl'    => $socialLinkUrl,
                'secretKey'		   => $secretKey,
                'nextGame'         => $nextGame
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
             $user = $this->zfcUserAuthentication()->getIdentity();
             $game = $sg->checkGame($identifier);
             $bonusEntry = $sg->addAnotherChance($game, $user, 1);
         }

         $response = $this->getResponse();
         $response->setContent(\Zend\Json\Json::encode(array(
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
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
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
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
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
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_lottery_service');
        }

        return $this->gameService;
    }
}
