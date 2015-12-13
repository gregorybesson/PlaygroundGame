<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Mission;
use PlaygroundGame\Controller\Frontend\GameController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use PlaygroundGame\Service\GameService;

class MissionController extends GameController
{

    /**
     * @var gameService
     */
    protected $gameService;
    protected $mission;
    
    /**
     * Homepage of the game
     */
    public function indexAction()
    {
        $isSubscribed = false;
    
        $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
        if ($entry) {
            $isSubscribed = true;
        }
    
        $games = $this->getGameService()->getMissionGames($this->game, $this->user);
        
        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
            'user'             => $this->user,
            'games'            => $games,
            'isSubscribed'     => $isSubscribed,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
        ));
    
        return $viewModel;
    }

    public function playAction()
    {
        $subGameIdentifier = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $subGame = $this->getGameService()->checkGame($subGameIdentifier);

        if(!$subGame){
            $games = $this->getGameService()->getMissionGames($this->game, $this->user);
            foreach($games as $k=>$v){
                $g = $v['game'];
                $entry = $v['entry'];
                if($g->getGame()->isStarted() && $g->getGame()->isOnline()){
                    $subGame = $g->getGame();
                    $subGameIdentifier = $subGame->getIdentifier();
                    break;
                }
            }
        }
        
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'mission',
            array('id' => $this->game->getIdentifier()),
            array('force_canonical' => true)
        );

        $session = new Container('facebook');
        // Redirect to fan gate if the game require to 'like' the page before playing
        if ($session->offsetExists('signed_request')) {
            if($this->game->getFbFan()){
                if ($this->getGameService()->checkIsFan($this->game) === false){
                    return $this->redirect()->toRoute(
                        $this->game->getClassType().'/fangate',
                        array('id' => $this->game->getIdentifier())
                    );
                }
            }
        }

        if (!$this->user) {

            // The game is deployed on Facebook, and played from Facebook : retrieve/register user
            if ($session->offsetExists('signed_request')) {

                // Get Playground user from Facebook info
                $viewModel = $this->buildView($this->game);
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch(
                    'playgrounduser_user',
                    array(
                        'controller' => 'playgrounduser_user',
                        'action' => 'registerFacebookUser',
                        'provider' => 'facebook'
                    )
                );

                $this->layout()->setTemplate($beforeLayout);
                $this->user = $view->user;

                // If the user cannot be created/retrieved from Facebook info, redirect to login/register form
                if (!$this->user){
                    $redirect = urlencode(
                        $this->frontendUrl()->fromRoute(
                            'mission/play',
                            array('id' => $this->game->getIdentifier()),
                            array('force_canonical' => true)
                        )
                    );
                    if(array_search('login', $this->game->getStepsArray())){
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute('mission/login') . '?redirect='.$redirect
                        );
                    } else {
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute('zfcuser/register') . '?redirect='.$redirect
                        );
                    } 
                }

                // The game is not played from Facebook : redirect to login/register form

            } elseif(!$this->game->getAnonymousAllowed()) {
                $redirect = urlencode(
                    $this->frontendUrl()->fromRoute(
                        'mission/play',
                        array('id' => $this->game->getIdentifier()),
                        array('force_canonical' => true)
                    )
                );

                if(array_search('login', $this->game->getStepsArray())){
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute('mission/login') . '?redirect='.$redirect
                    );
                } else {
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute('zfcuser/register') . '?redirect='.$redirect
                    );
                }
            }
        }

        $entry = $this->getGameService()->play($this->game, $this->user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('You have already played');

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'mission/result',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }
        
        $beforeLayout = $this->layout()->getTemplate();
        $subViewModel = $this->forward()->dispatch(
            'playgroundgame_'.$subGame->getClassType(),
            array(
                'controller' => 'playgroundgame_'.$subGame->getClassType(),
                'action' => 'play',
                'id' => $subGameIdentifier
            )
        );
        
        if($this->getResponse()->getStatusCode() == 302){
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'mission/result',
                    array(
                        'id' => $this->game->getIdentifier(),
                        'gameId' => $subGameIdentifier
                    ),
                    array('force_canonical' => true)
                )
            );
        }
        
        // suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);
        
        // give the ability to the mission to have its customized look and feel.
        $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        // I've already added the path for the game so the base path is $l[1]
        $templatePathResolver->addPath($l[1].'custom/'.$this->game->getIdentifier());
        
        $subViewModel->mission = $this->game;
        
        return $subViewModel;
    }

    public function resultAction()
    {
        $statusMail = null;
        
        $subGameIdentifier = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $subGame = $this->getGameService()->checkGame($subGameIdentifier);

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'mission',
            array('id' => $game->getIdentifier()),
            array('force_canonical' => true)
        ).'?key='.$secretKey;

        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        //TODO check existence of an active entry
        /*$lastEntry = $this->getGameService()->findLastInactiveEntry($game, $user, $this->params()->fromQuery('anonymous_identifier'));
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('mission', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
        }*/

        if (!$this->user && !$this->game->getAnonymousAllowed()) {
            $redirect = urlencode(
                $this->frontendUrl()->fromRoute(
                    'mission/result',
                    array('id' => $game->getIdentifier())
                )
            );

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'zfcuser/register',
                    array('channel' => $channel)
                ) . '?redirect='.$redirect
            );
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        /*
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                    //$bonusEntry = $this->getGameService()->addAnotherChance($game, $user, 1);
                }
            }
        }
        */

        // buildView must be before sendMail because it adds the game template path to the templateStack
        // TODO : Improve this.
        $viewModel = $this->buildView($this->game);
        
        //$this->sendMail($game, $user, $lastEntry);

        $beforeLayout = $this->layout()->getTemplate();
        $subViewModel = $this->forward()->dispatch(
            'playgroundgame_'.$subGame->getClassType(),
            array(
                'controller' => 'playgroundgame_'.$subGame->getClassType(),
                'action' => 'result',
                'id' => $subGameIdentifier
            )
        );
        
        if($this->getResponse()->getStatusCode() == 302){
            $this->getResponse()->setStatusCode('200');
            $urlRedirect = $this->getResponse()->getHeaders()->get('Location');
            
            $subViewModel = $this->forward()->dispatch(
                'playgroundgame_'.$subGame->getClassType(),
                array(
                    'controller' => 'playgroundgame_'.$subGame->getClassType(),
                    'action' => 'result',
                    'id' => $subGameIdentifier
                )
            );
        }
        // suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);

        // give the ability to the mission to have its customized look and feel.
        $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        // I've already added the path for the game so the base path is $l[1]
        $templatePathResolver->addPath($l[1].'custom/'.$this->game->getIdentifier());
        
        $subViewModel->mission = $this->game;
        return $subViewModel;
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
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_mission_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }
}
