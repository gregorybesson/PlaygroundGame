<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Entry;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\Form;

class InstantWinController extends GameController
{
    
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

        if($game->getOccurrenceType()=='datetime'){
            return $this->playDatetime($identifier, $user, $game, $session, $channel);
        }elseif($game->getOccurrenceType()=='code'){
            return $this->playCode($identifier, $game, $session, $channel);
        }
    }

    public function playDatetime($identifier, $user, $game, $session, $channel)
    {
        $sg = $this->getGameService();

        if (!$user) {

            // The game is deployed on Facebook, and played from Facebook : retrieve/register user

            if ($channel == 'facebook' && $session->offsetExists('signed_request')) {

                // Get Playground user from Facebook info

                $viewModel = $this->buildView($game);
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user', 'action' => 'registerFacebookUser'));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
                }

            // The game is not played from Facebook : redirect to login/register form

            } else {
                $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
            }

        }
        
        $viewModel = $this->buildView($game);        

        $beforeLayout = $this->layout()->getTemplate();
        // je délègue la responsabilité du formulaire à PlaygroundUser, y compris dans sa gestion des erreurs
        $form = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user', 'action' => 'address'));

        // TODO : suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);
        // Le formulaire est validé, il renvoie true et non un ViewModel
        if (!($form instanceof \Zend\View\Model\ViewModel)) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result', array('id' => $identifier, 'channel' => $channel)));
        }

        if ($this->getRequest()->isPost()) {
            // En post, je reçois la maj du form pour les gagnants. Je n'ai pas à créer une nouvelle participation mais vérifier la précédente
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if (!$lastEntry) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
            }
            $winner = $lastEntry->getWinner();
            // if not winner, I'm not authorized to call this page in POST mode.
            if (!$winner) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
            }

            // si la requete est POST et que j'arrive ici, c'est que le formulaire contient une erreur. Donc je prépare la vue formulaire sans le grattage
            //$viewModel->setTemplate('instant-win/winner/form');
        } else {
            // J'arrive sur le jeu, j'essaie donc de participer
            $entry = $sg->play($game, $user);
            if (!$entry) {
                // the user has already taken part of this game and the participation limit has been reached
                $this->flashMessenger()->addMessage('Vous avez déjà participé');

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result',array('id' => $identifier, 'channel' => $channel)));
            }

            // update the winner attribute in entry.
            $winner = $sg->IsInstantWinner($game, $user);
        }

        $viewModel->setVariables(array(
            'game' => $game,
            'winner' => $winner,
            'flashMessages' => $this->flashMessenger()->getMessages()
        ));
        $viewModel->addChild($form, 'form');
            
        return $viewModel;
    }

    public function playCode($identifier, $game, $session, $channel)
    {
        $sg = $this->getGameService();

        $viewModel = $this->buildView($game);
        
        $viewModel->setTemplate('playground-game/instant-win/play-code');
        $form = new Form('CodeForm');
        $form->setAttribute('method', 'post');
        $form->setAttribute('action', '');
        $form->add(array(
            'type' => 'Zend\Form\Element\Text',
            'name' => 'code-input',
            'options' => array(
                'label' => 'Entrez votre code',
                ),
            'attributes' => array(
                'placeholder' => 'Code', 
                )
            ));

        $form->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Participer',
            ),
        ));
        
        if ($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $code = $form->getData()['code-input'];
                if (empty($code)) {
                    $this->flashMessenger()->addMessage('Vous devez entrer un code avant de valider !');
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }
                $code = trim($code);
                $occurrence_mapper = $sg->getInstantWinOccurrenceMapper();
                $matching_occurrences = $occurrence_mapper->findBy(array(
                    'instantwin' => $game,
                    'occurrence_value' => $code,
                ));
                if (empty($matching_occurrences)) {
                    $this->flashMessenger()->addMessage('Désolé mais le code entré est invalide !');
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }
                elseif (count($matching_occurrences)==1) {
                    $occurrence = array_shift($matching_occurrences);
                    if ($occurrence->getEntry()) {
                        $this->flashMessenger()->addMessage('Le code entré a déjà été utilisé, vous ne pouvez pas rejouer avec le même code !');
                        return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    }
                    $session = new \Zend\Session\Container('zfcuser');
                    $session->offsetSet('occurrence',$occurrence->getId());
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result',array('id' => $identifier, 'channel' => $channel)));
                }
            }
        }
        $viewModel->setVariables(array(
            'game' => $game,
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages()
        ));
        return $viewModel;
    }

    public function resultAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        $user   = $this->zfcUserAuthentication()->getIdentity();
        $sg     = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }
        
        $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
        if ($lastEntry || $game->getOccurrenceType()=='datetime') {
            return $this->resultDatetime($user, $game, $channel, $lastEntry);
        } elseif ($game->getOccurrenceType()=='code') {
            return $this->resultCode($user, $game, $channel);
        } else{
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
        }
    }

    public function resultCode($user, $game, $channel)
    {
        $sg     = $this->getGameService();
        $session = new \Zend\Session\Container('zfcuser');
        if (!$session->offsetExists('occurrence')){
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
        }
        
        $viewModel = $this->buildView($game);
        $occurrence_mapper = $sg->getInstantWinOccurrenceMapper();
        $occurrence = $occurrence_mapper->findById($session->offsetGet('occurrence'));
        $winner = $occurrence->getWinning();
        if(!$user){
            $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }
        $beforeLayout = $this->layout()->getTemplate();
        $form_register = $this->forward()->dispatch('playgrounduser_user', array('action' => 'address'));
        $this->layout()->setTemplate($beforeLayout);
        
        $viewModel->setTemplate('playground-game/instant-win/result-code');
        if ($form_register instanceof \Zend\View\Model\ViewModel) {
            $viewModel->addChild($form_register, 'form_register');
            $viewModel->setVariables(array(
                'game'             => $game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
                'winner'           => $winner,
            ));
            return $viewModel;
        }
        $entry = $sg->play($game, $user);
        if (!$entry){
            $this->flashMessenger()->addMessage('Vous avez déjà participé !');
            return $this->resultDatetime($user, $game, $channel);
        }
        $occurrence->setEntry($entry);
        $occurrence_mapper->update($occurrence);
        $winner = $sg->isCodeInstantWinner($game, $user, $occurrence);
        return $this->resultDatetime($user, $game, $channel);
    }

    public function resultDatetime($user, $game, $channel, $lastEntry){
        $sg     = $this->getGameService();

        $statusMail = null;

        $secretKey = strtoupper(substr(sha1($user->getId().'####'.time()),0,15));
        $socialLinkUrl = $this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        if (!$user) {
            $redirect = urlencode($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register') . '?redirect='.$redirect);
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

        $nextGame = parent::getMissionGameService()->checkCondition($game, $winner, true, $lastEntry);

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
            'game'             => $game,
            'winner'           => $winner,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'form'             => $form,
            'socialLinkUrl'    => $socialLinkUrl,
            'secretKey'        => $secretKey,
            'nextGame'         => $nextGame
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
}