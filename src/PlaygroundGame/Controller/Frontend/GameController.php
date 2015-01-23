<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use PlaygroundGame\Service\GameService;
use PlaygroundGame\Service\Prize as PrizeService;
use Zend\View\Model\JsonModel;

class GameController extends AbstractActionController
{

    /**
     * @var gameService
     */
    protected $gameService;

    protected $prizeService;

    protected $options;
    
    protected $loginForm;

    /**
     * This action acts as a hub : Depending on the first step of the game, it will forward the action to this step 
     */
    public function homeAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');

        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // This fix exists only for safari in FB on Windows : we need to redirect the user to the page outside of iframe
        // for the cookie to be accepted. PlaygroundCore redirects to the FB Iframed page when
        // it discovers that the user arrives for the first time on the game in FB.
        // When core redirects, it adds a 'redir_fb_page_id' var in the querystring
        // Here, we test if this var exist, and then send the user back to the game in FB.
        // Now the cookie will be accepted by Safari...
        $pageId = $this->params()->fromQuery('redir_fb_page_id');
        if (!empty($pageId)) {
            $appId = 'app_'.$game->getFbAppId();
            $url = '//www.facebook.com/pages/game/'.$pageId.'?sk='.$appId;

            return $this->redirect()->toUrl($url);
        }

        // If an entry has already been done during this session, I reset the anonymous_identifier cookie
        // so that another person can play the same game (if game conditions are fullfilled)
        $session = new Container('anonymous_identifier');
        if ($session->offsetExists('anonymous_identifier')) {
            $session->offsetUnset('anonymous_identifier');
        }
        
        return $this->forward()->dispatch('playgroundgame_'.$game->getClassType(), array('controller' => 'playgroundgame_'.$game->getClassType(), 'action' => $game->firstStep(), 'id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

    }

    /**
     * Homepage of the game
     */
    public function indexAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        $isSubscribed = false;

         // Determine if the play button should be a CTA button (call to action)
        $isCtaActive = false;

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // If on Facebook, check if you have to be a FB fan to play the game

        if ($channel == 'facebook') {
            if ($game->getFbFan()) {
            	$isFan = $sg->checkIsFan($game);  
            	if (!$isFan) {
            		return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('' . $game->getClassType().'/fangate',array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            	}
            }

            $isCtaActive = true;
        }


        $entry = $sg->checkExistingEntry($game, $user);
        if ($entry) {
            $isSubscribed = true;
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game'             => $game,
            'isSubscribed'     => $isSubscribed,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'isCtaActive'      => $isCtaActive,
        ));

        return $viewModel;
    }

    /**
     * This action has been designed to be called by other controllers
     * It gives the ability to display an information form and persist it in the game entry
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function registerAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        $form = $sg->createFormFromJson($game->getPlayerForm()->getForm());

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            
            $form->setData($data);

            if ($form->isValid()) {
                $dataJson = json_encode($form->getData());
                
                $steps = $game->getStepsArray();
                $viewSteps = $game->getStepsViewsArray();
                $key = array_search($this->params('action'), $viewSteps);
                if (!$key) {
                  $key = array_search($this->params('action'), $steps);
                }
                $keyplay = array_search('play', $steps);

                // If register step before play, I don't have no entry yet. I have to create one
                // If register after play step, I search for the last entry created by play step.

                if($key && $key < $keyplay){
                    
                    if($game->getAnonymousAllowed() && $game->getAnonymousIdentifier() && isset($data[$game->getAnonymousIdentifier()])){
                        $anonymousIdentifier = $data[$game->getAnonymousIdentifier()];
                        
                        // I must transmit this info during the whole game workflow
                        $session = new Container('anonymous_identifier');
                        $session->offsetSet('anonymous_identifier',  $anonymousIdentifier);
                    }
                    $entry = $sg->play($game, $user);
                    if (!$entry) {
                        // the user has already taken part of this game and the participation limit has been reached
                        $this->flashMessenger()->addMessage('Vous avez déjà participé');
                    
                        return $this->redirect()->toUrl($this->frontendUrl()->fromRoute($game->getClassType().'/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
                    }
                }else{
                    // I'm looking for an entry without anonymousIdentifier (the active entry in fact).
                    $entry = $sg->findLastEntry($game, $user);
                    if($game->getAnonymousAllowed() && $game->getAnonymousIdentifier() && isset($data[$game->getAnonymousIdentifier()])){
                        
                        $anonymousIdentifier = $data[$game->getAnonymousIdentifier()];
                        
                        $entry->setAnonymousIdentifier($anonymousIdentifier);
                        
                        // I must transmit this info during the whole game workflow
                        $session = new Container('anonymous_identifier');
                        $session->offsetSet('anonymous_identifier',  $anonymousIdentifier);
                        
                    }
                    if ($sg->hasReachedPlayLimit($game, $user)){
                        // the user has already taken part of this game and the participation limit has been reached
                        $this->flashMessenger()->addMessage('Vous avez déjà participé');
                    
                        return $this->redirect()->toUrl($this->frontendUrl()->fromRoute($game->getClassType().'/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
                    }
                }
                
                $entry->setPlayerData($dataJson);
                $sg->getEntryMapper()->update($entry);

                return $this->redirect()->toUrl($this->frontendUrl()->fromRoute($game->getClassType() .'/' . $game->nextStep($this->params('action')), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'form' => $form,
            'title' => $game->getPlayerForm()->getTitle(),
            'description' => $game->getPlayerForm()->getDescription(),
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    /**
     * This action takes care of the terms of the game
     */
    public function termsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    /**
     * This action takes care of the conditions of the game
     */
    public function conditionsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    /**
     * This action takes care of bounce page of the game
     */
    public function bounceAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $availableGames = $sg->getAvailableGames($user);

        $rssUrl = '';
        $config = $sg->getServiceManager()->get('config');
        if (isset($config['rss']['url'])) {
            $rssUrl = $config['rss']['url'];
        }

		$viewModel = $this->buildView($game);
		$viewModel->setVariables(array(
		    'rssUrl'         => $rssUrl,
            'game'           => $game,
            'user'           => $user,
            'availableGames' => $availableGames,
            'flashMessages'  => $this->flashMessenger()->getMessages(),
		));

        return $viewModel;
    }

    /**
     * Send mail for winner and/or loser
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param \PlaygroundGame\Entity\Entry $lastEntry
     * @param \PlaygroundGame\Entity\Prize $prize
     */
    public function sendMail($game, $user, $lastEntry, $prize = NULL){
        if(($user || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) && $game->getMailWinner() && $lastEntry->getWinner()){

            $this->getGameService()->sendResultMail($game, $user, $lastEntry, 'winner', $prize);
        }

        if(($user || ($game->getAnonymousAllowed() && $game->getAnonymousIdentifier())) && $game->getMailLooser() && !$lastEntry->getWinner()){
            
            $this->getGameService()->sendResultMail($game, $user, $lastEntry, 'looser');
        }
    }

    /**
     * 
     * @param \PlaygroundGame\Entity\Game $game
     * @param \PlaygroundUser\Entity\User $user
     * @param string $channel
     */
    public function checkFbRegistration($user, $game, $channel)
    {
        $redirect = false;
        $session = new Container('facebook');
        $sg = $this->getGameService();
        if ($channel == 'facebook' && $session->offsetExists('signed_request')) {
            if (!$user) {
                // Get Playground user from Facebook info
                $beforeLayout = $this->layout()->getTemplate();
                $view = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user', 'action' => 'registerFacebookUser', 'provider' => $channel));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirectUrl = urlencode($this->frontendUrl()->fromRoute(''. $game->getClassType() .'/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    $redirect =  $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirectUrl);
                }

            }

            if($game->getFbFan()){
                if ($sg->checkIsFan($game) === false){
                    $redirect =  $this->redirect()->toRoute($game->getClassType().'/fangate',array('id' => $game->getIdentifier()));
                }
            }
        }

        return $redirect;
    }

    /**
     * This method create the basic Game view
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function buildView($game)
    {
        $viewModel = new ViewModel();

        $this->addMetaTitle($game);
        $this->addMetaBitly();
        $this->addGaEvent($game);

        $this->customizeGameDesign($game);
        
        // this is possible to create a specific game design in /design/frontend/default/custom. It will precede all others templates.
        $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        $templatePathResolver->addPath($l[0].'custom/'.$game->getIdentifier());
        
        $view = $this->addAdditionalView($game);
        if ($view and $view instanceof \Zend\View\Model\ViewModel) {
            $viewModel->addChild($view, 'additional');
        } elseif ($view && $view instanceof \Zend\Http\PhpEnvironment\Response) {
            return $view;
        }

        $this->layout()->setVariables(
            array(
                'game' => $game, 
                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
            )
        );
        
        $viewModel->setVariables($this->getShareData($game));

        return $viewModel;
    }

    public function addAdditionalView($game)
    {
        $view = false;
        $actionName = $this->getEvent()->getRouteMatch()->getParam('action', 'not-found');
        $stepsViews = json_decode($game->getStepsViews(), true);
        if($stepsViews && isset($stepsViews[$actionName]) && is_string($stepsViews[$actionName])){
            $action = $stepsViews[$actionName];
            $beforeLayout = $this->layout()->getTemplate();
            $view = $this->forward()->dispatch('playgroundgame_game', array('action' => $action, 'id' => $game->getIdentifier()));
            // suite au forward, le template de layout a changé, je dois le rétablir...
            $this->layout()->setTemplate($beforeLayout);

        }

        return $view;
    }

    public function addMetaBitly()
    {
        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);
    }

    public function addGaEvent($game)
    {
        // Google Analytics event
        $ga = $this->getServiceLocator()->get('google-analytics');
        $event = new \PlaygroundCore\Analytics\Event($game->getClassType(), $this->params('action'));
        $event->setLabel($game->getTitle());
        $ga->addEvent($event);
    }

    public function addMetaTitle($game)
    {
        $title = $game->getTitle();
        // Meta set in the layout
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $title,
                'currentPage' => array(
                    'pageGames' => 'games',
                    'pageWinners' => ''
                ),
                'headParams' => array(
                    'headTitle' => $title,
                    'headDescription' => $title,
                ),
                'bodyCss' => $game->getIdentifier()
            )
        );
    }

    public function customizeGameDesign($game)
    {
        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
        }
    }

    public function getShareData($game)
    {
        $fo = $this->getServiceLocator()->get('facebook-opengraph');
        // I change the fbappid if i'm in fb
        if($this->getEvent()->getRouteMatch()->getParam('channel') === 'facebook'){
            $fo->setId($game->getFbAppId());
        }

        // If I want to add a share block in my view
        if ($game->getFbShareMessage()) {
            $fbShareMessage = $game->getFbShareMessage();
        } else {
            $fbShareMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage());
        }

        if ($game->getFbShareImage()) {
            $fbShareImage = $this->frontendUrl()->fromRoute('', array('channel' => ''), array('force_canonical' => true), false) . $game->getFbShareImage();
        } else {
            $fbShareImage = $this->frontendUrl()->fromRoute('', array('channel' => ''), array('force_canonical' => true), false) . $game->getMainImage();
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));

        // Without bit.ly shortener
        $socialLinkUrl = $this->frontendUrl()->fromRoute($game->getClassType(), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true));
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        // FB Requests only work when it's a FB app
        if ($game->getFbRequestMessage()) {
            $fbRequestMessage = urlencode($game->getFbRequestMessage());
        } else {
            $fbRequestMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage());
        }

        if ($game->getTwShareMessage()) {
            $twShareMessage = $game->getTwShareMessage() . $socialLinkUrl;
        } else {
            $twShareMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage()) . $socialLinkUrl;
        }

        $ogTitle = new \PlaygroundCore\Opengraph\Tag('og:title', $fbShareMessage);
        $ogImage = new \PlaygroundCore\Opengraph\Tag('og:image', $fbShareImage);
        
        $fo->addTag($ogTitle);
        $fo->addTag($ogImage);
        
        $data = array(
            'socialLinkUrl'       => $socialLinkUrl,
            'secretKey'           => $secretKey,
            'fbShareMessage'      => $fbShareMessage,
            'fbShareImage'        => $fbShareImage,
            'fbRequestMessage'    => $fbRequestMessage,
            'twShareMessage'      => $twShareMessage,
        );

        return $data;
    }

    /**
     * This action displays the Prizes page associated to the game
     */
    public function prizesAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$sg = $this->getGameService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

    	if (count($game->getPrizes()) == 0){
    		return $this->notFoundAction();
    	}

    	$viewModel = $this->buildView($game);
    	$viewModel->setVariables(
    		array(
    			'game'             => $game,
    			'flashMessages'    => $this->flashMessenger()->getMessages(),
    		)
    	);

    	return $viewModel;
    }

    /**
     * This action displays a specific Prize page among those associated to the game
     */
    public function prizeAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$prizeIdentifier = $this->getEvent()->getRouteMatch()->getParam('prize');
    	$sg = $this->getGameService();
    	$sp = $this->getPrizeService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

		$prize = $sp->getPrizeMapper()->findByIdentifier($prizeIdentifier);
		
		if (!$prize) {
			return $this->notFoundAction();
		}


    	$viewModel = $this->buildView($game);
    	$viewModel->setVariables(
    		array(
    			'game'             => $game,
    			'prize'     	   => $prize,
  				'flashMessages'    => $this->flashMessenger()->getMessages(),
   			)
    	);

    	return $viewModel;
    }

    public function gameslistAction()
    {

        $layoutViewModel = $this->layout();

        $slider = new ViewModel();
        $slider->setTemplate('playground-game/common/top_promo');

        $sliderItems = $this->getGameService()->getActiveSliderGames();

        $slider->setVariables(array('sliderItems' => $sliderItems));

        $layoutViewModel->addChild($slider, 'slider');

        $games = $this->getGameService()->getActiveGames(false,'','endDate');
        if (is_array($games)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($games));
        } else {
            $paginator = $games;
        }

        $paginator->setItemCountPerPage(7);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

        $this->layout()->setVariables(
           array(
            'sliderItems'   => $sliderItems,
            /*'adserving'       => array(
                'cat1' => 'playground',
                'cat2' => 'game',
                'cat3' => ''
            ),*/
            'currentPage' => array(
                'pageGames' => 'games',
                'pageWinners' => ''
            ),
           )
        );

        return new ViewModel(
            array(
                'games'       => $paginator
            )
        );
    }

    public function fangateAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
        }

        // I change the label in the breadcrumb ...
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $game->getTitle(),
                'headParams' => array(
                    'headTitle' => $game->getTitle(),
                    'headDescription' => $game->getTitle(),
                ),
            )
        );

        $viewModel = new ViewModel(
            array(
                'game'             => $game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
            )
        );

        return $viewModel;
    }
    
    public function shareAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
    
        $statusMail = null;
    
        if (!$identifier) {
            return $this->notFoundAction();
        }
    
        $gameMapper = $this->getGameService()->getGameMapper();
        $game = $gameMapper->findByIdentifier($identifier);
    
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }
    
        // Has the user finished the game ?
        $lastEntry = $this->getGameService()->findLastInactiveEntry($game, $user);
    
        if ($lastEntry === null) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }
    
        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('postvote/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
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
                }
            }
        }
    
        // buildView must be before sendMail because it adds the game template path to the templateStack
        $viewModel = $this->buildView($game);
    
        $this->sendMail($game, $user, $lastEntry);
    
        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
            'game'             => $game,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'form'             => $form,
        ));
    
        return $viewModel;
    }
    
    public function fbshareAction()
    {
        $viewModel = new JsonModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $this->errorJson();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->errorJson();
        }
        if (!$fbId) {
            return $this->errorJson();
        }
    
        $sg->postFbWall($fbId, $game, $user, $entry);
    
        return $this->successJson();
    
    }
    
    public function fbrequestAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $to = $this->params()->fromQuery('to');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $this->errorJson();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->errorJson();
        }
        if (!$fbId) {
            return $this->errorJson();
        }
    
        $sg->postFbRequest($fbId, $game, $user, $entry, $to);
    
        return $this->successJson();
    
    }
    
    public function tweetAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $tweetId = $this->params()->fromQuery('tweetId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $this->errorJson();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->errorJson();
        }
        if (!$tweetId) {
            return $this->errorJson();
        }
    
        $sg->postTwitter($tweetId, $game, $user, $entry);
    
        return $this->successJson();
    
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
            return $this->errorJson();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->errorJson();
        }
        if (!$googleId) {
            return $this->errorJson();
        }
    
        $sg->postGoogle($googleId, $game, $user, $entry);
    
        return $this->successJson();
    
    }
    
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getLoginForm();
        
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        
        $sg = $this->getGameService();
        
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }
    
        if ($request->isPost()) {
            
            $form->setData($request->getPost());
            
            if (!$form->isValid()) {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage('Authentication failed. Please try again.');
            
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/' . $game->getClassType() . '/login', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))).($redirect ? '?redirect='.$redirect : ''));
            }
            
            // clear adapters
            $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
            $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

            $logged = $this->forward()->dispatch('playgrounduser_user', array('action' => 'ajaxauthenticate'));

            if ($logged) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/' . $game->getClassType() . '/' . $game->nextStep('index'), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            } else {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage('Authentication failed. Please try again.');
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/' . $game->getClassType() . '/login', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }
        }
        
        $form->setAttribute('action', $this->url()->fromRoute('frontend/'.$game->getClassType().'/login', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game'             => $game,
            'form'             => $form,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
        ));
        return $viewModel;
    }
    
    /**
     * return ajax response in json format
     *
     * @param array $data
     * @return \Zend\View\Model\JsonModel
     */
    protected function successJson($data = null)
    {
        $model = new JsonModel(array(
            'success' => true,
            'data' => $data
        ));
        return $model->setTerminal(true);
    }
    
    /**
     * return ajax response in json format
     *
     * @param string $message
     * @return \Zend\View\Model\JsonModel
     */
    protected function errorJson($message = null)
    {
        $model = new JsonModel(array(
            'success' => false,
            'message' => $message
        ));
        return $model->setTerminal(true);
    }

    /**
     * @param string $helperName
     */
    protected function getViewHelper($helperName)
    {
        return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_lottery_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }

    public function getPrizeService()
    {
    	if (!$this->prizeService) {
    		$this->prizeService = $this->getServiceLocator()->get('playgroundgame_prize_service');
    	}

    	return $this->prizeService;
    }

    public function setPrizeService(PrizeService $prizeService)
    {
    	$this->prizeService = $prizeService;

    	return $this;
    }

    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions($this->getServiceLocator()->get('playgroundcore_module_options'));
        }

        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
    
    public function getLoginForm()
    {
        if (!$this->loginForm) {
            $this->setLoginForm($this->getServiceLocator()->get('zfcuser_login_form'));
        }
        return $this->loginForm;
    }
    
    public function setLoginForm($loginForm)
    {
        $this->loginForm = $loginForm;
        $fm = $this->flashMessenger()->setNamespace('zfcuser-login-form')->getMessages();
        if (isset($fm[0])) {
            $this->loginForm->setMessages(
                array('identity' => array($fm[0]))
            );
        }
        return $this;
    }
}
