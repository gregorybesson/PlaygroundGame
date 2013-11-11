<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Lottery;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class GameController extends AbstractActionController
{

    /**
     * @var gameService
     */
    protected $gameService;

    protected $prizeService;

    protected $missionGameService;
    
    protected $options;

    public function homeAction()
    {
    
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }
    
        return $this->forward()->dispatch('playgroundgame_'.$game->getClassType(), array('controller' => 'playgroundgame_'.$game->getClassType(), 'action' => $game->firstStep(), 'id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));
        
    }
    
    public function indexAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $isSubscribed = false;
        
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // If on Facebook, check if you have to be a FB fan to play the game
        if($game->getFbFan()){
        	$isFan = $sg->checkIsFan($game);
        	if(!$isFan){
        		return $this->redirect()->toUrl($this->url()->fromRoute('frontend/' . $game->getClassType().'/fangate',array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        	}
        }

        // Determine if the play button should be a CTA button (call to action)
        $isCtaActive = false;
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        if ($channel == 'facebook'){
            $isCtaActive = true;
        }

        $subscription = $sg->checkExistingEntry($game, $user);
        if ($subscription) {
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

    // TODO : Sanitize and factorize
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

        // Want to be sure that a least one finished entry has been done by this user
        $subscription = $sg->checkExistingEntry($game, $user, false);
            if (!$subscription) {
            // the user is not registered yet.
            $redirect = urlencode($this->url()->fromRoute('frontend/lottery', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
        }

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
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'form'             => $form,
        ));

        return $viewModel;
    }
    
    public function registerAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();
        
        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }
        
        // je délègue la responsabilité du formulaire à PlaygroundUser, y compris dans sa gestion des erreurs
        // I have to express controller and action params for the layout to be correctly updated
        $formRegister = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user', 'action' => 'address'));
        
        // Le formulaire est validé, il renvoie true et non un ViewModel
        if (!($formRegister instanceof \Zend\View\Model\ViewModel)) {
            
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/'. $game->getClassType() . '/'. $game->nextStep($this->params('action')), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }
        
        return $formRegister;
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

    public function fbrequestAction()
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

        $sg->postFbRequest($fbId, $game, $user);

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

    public function termsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
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

    public function conditionsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
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

    public function buildView($game)
    {
        $sg = $this->getGameService();
        $ga = $this->getServiceLocator()->get('google-analytics');
        $event = new \PlaygroundCore\Analytics\Event($game->getClassType(), $this->params('action'));
        $event->setLabel($game->getTitle());
        $viewModel = new ViewModel();

        $ga->addEvent($event);
        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
        }

        // I change the label of the quiz in the breadcrumb ...
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $game->getTitle(),
                'currentPage' => array(
                    'pageGames' => 'games',
                    'pageWinners' => ''
                ),
                'headParams' => array(
                    'headTitle' => $game->getTitle(),
                    'headDescription' => $game->getTitle(),
                ),
            )
        );
        
        // REGISTER FORM INCLUDED IN EXISTING CONTROLLERS VIEWS
        /*
        $beforeLayout = $this->layout()->getTemplate();
        // je délègue la responsabilité du formulaire à PlaygroundUser, y compris dans sa gestion des erreurs
        $form = $this->forward()->dispatch('playgrounduser_user', array('action' => 'address'));
        
        // TODO : suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);
        $viewModel->addChild($form, 'formRegister');
        */

        if($this->getEvent()->getRouteMatch()->getParam('channel') === 'facebook'){
            $fo = $this->getServiceLocator()->get('facebook-opengraph');
            $fo->setId($game->getFbAppId());
        }

        if ($game->getFbShareMessage()) {
            $fbShareMessage = $game->getFbShareMessage();
        } else {
            $fbShareMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage());
        }

        if ($game->getFbShareImage()) {
            $fbShareImage = $this->url()->fromRoute('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)) . $game->getFbShareImage();
        } else {
            $fbShareImage = $this->url()->fromRoute('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)) . $game->getMainImage();
        }

        $secretKey = strtoupper(substr(sha1($game->getId().'####'.time()),0,15));

        // Without bit.ly shortener
        $socialLinkUrl = $this->url()->fromRoute('frontend/' . $game->getClassType(), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true));
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

        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('og:title', $fbShareMessage);
        $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);
        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

        // right column
        $column = new ViewModel();
        $column->setTemplate($this->layout()->col_right);
        $column->setVariables(array('game' => $game));

        $this->layout()->addChild($column, 'column_right');
        
        $viewModel->setVariables(array(
            'socialLinkUrl'       => $socialLinkUrl,
            'secretKey'           => $secretKey,
            'fbShareMessage'      => $fbShareMessage,
            'fbShareImage'        => $fbShareImage,
            'fbRequestMessage'    => $fbRequestMessage,
            'twShareMessage'      => $twShareMessage,
        ));
        
        return $viewModel;
    }

    public function prizesAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$user = $this->zfcUserAuthentication()->getIdentity();
    	$sg = $this->getGameService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

    	if (count($game->getPrizes()) == 0){
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

    	/*$adserving = $this->getOptions()->getAdServing();
    	$adserving['cat2'] = 'game';
    	$adserving['cat3'] = '&EASTgameid='.$game->getId();*/
    	// I change the label in the breadcrumb ...
    	$this->layout()->setVariables(
			array(
				'breadcrumbTitle' => $game->getTitle(),
				//'adserving'       => $adserving,
				'currentPage' => array(
						'pageGames' => 'games',
						'pageWinners' => ''
				),
                'headParams' => array(
                    'headTitle' => $game->getTitle(),
                    'headDescription' => $game->getTitle(),
                ),
			)
    	);

    	$bitlyclient = $this->getOptions()->getBitlyUrl();
    	$bitlyuser = $this->getOptions()->getBitlyUsername();
    	$bitlykey = $this->getOptions()->getBitlyApiKey();

    	$this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

    	// right column
    	$column = new ViewModel();
    	$column->setTemplate($this->layout()->col_right);
    	$column->setVariables(array('game' => $game));

    	$this->layout()->addChild($column, 'column_right');

    	$viewModel = new ViewModel(
    		array(
    			'game'             => $game,
    			'flashMessages'    => $this->flashMessenger()->getMessages(),
    		)
    	);

    	return $viewModel;
    }

    public function prizeAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$user = $this->zfcUserAuthentication()->getIdentity();
    	$sg = $this->getGameService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

    	$prizeIdentifier = $this->getEvent()->getRouteMatch()->getParam('prize');
		$sp = $this->getPrizeService();

		$prize = $sp->getPrizeMapper()->findByIdentifier($prizeIdentifier);
		if (!$prize) {
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
    					'currentPage' => array(
    							'pageGames' => 'games',
    							'pageWinners' => ''
    					),
                        'headParams' => array(
                            'headTitle' => $game->getTitle(),
                            'headDescription' => $game->getTitle(),
                        ),
    			)
    	);

    	$bitlyclient = $this->getOptions()->getBitlyUrl();
    	$bitlyuser = $this->getOptions()->getBitlyUsername();
    	$bitlykey = $this->getOptions()->getBitlyApiKey();

    	$this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

    	// right column
    	$column = new ViewModel();
    	$column->setTemplate($this->layout()->col_right);
    	$column->setVariables(array('game' => $game));

    	$this->layout()->addChild($column, 'column_right');

    	$viewModel = new ViewModel(
    		array(
    			'game'             => $game,
    			'prize'     	   => $prize,
  				'flashMessages'    => $this->flashMessenger()->getMessages(),
   			)
    	);

    	return $viewModel;
    }

    public function jeuxconcoursAction()
    {

        $layoutViewModel = $this->layout();
        $layoutViewModel->setTemplate('layout/jeuxconcours-2columns-right.phtml');

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

    public function getMissionGameService()
    {
        if (!$this->missionGameService) {
            $this->missionGameService = $this->getServiceLocator()->get('playgroundgame_mission_game_service');
        }

        return $this->missionGameService;
    }

    public function setMissionGameService(GameService $missionGameService)
    {
        $this->missionGameService = $missionGameService;

        return $this;
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
}