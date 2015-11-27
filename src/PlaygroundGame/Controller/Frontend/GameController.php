<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use PlaygroundGame\Service\GameService;
use PlaygroundGame\Service\Prize as PrizeService;
use Zend\View\Model\JsonModel;
use Zend\Http\PhpEnvironment\Response;
use Zend\Stdlib\Parameters;

class GameController extends AbstractActionController
{
    /**
     * @var \PlaygroundGame\Service\GameService
     */
    protected $gameService;

    protected $prizeService;

    protected $options;

    /**
     * Action called if matched action does not exist
     * For this view not to be catched by Zend\Mvc\View\RouteNotFoundStrategy
     * it has to be rendered in the controller. Hence the code below.
     *
     * This action is injected as a catchall action for each custom_games definition
     * This way, when a custom_game is created, the 404 is it's responsability and the
     * view can be defined in design/frontend/default/custom/$slug/playground_game/$gametype/404.phtml
     *
     *
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function notFoundAction()
    {
        $sg             = $this->getGameService();
        $identifier     = $this->getEvent()->getRouteMatch()->getParam('id');
        $viewRender     = $this->getServiceLocator()->get('ViewRenderer');

        $this->getEvent()->getRouteMatch()->setParam('action', 'not-found');
        $this->response->setStatusCode(404);

        $game = $sg->checkGame($identifier);

        $res = 'error/404';

        $viewModel = $this->buildView($game);
        $viewModel->setTemplate($res);

        $this->layout()->setVariable("content", $viewRender->render($viewModel));
        $this->response->setContent($viewRender->render($this->layout()));

        return $this->response;
    }

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

        // This fix exists only for safari in FB on Windows : we need to redirect the user to the page
        // outside of iframe for the cookie to be accepted. PlaygroundCore redirects to the FB Iframed page when
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
        
        return $this->forward()->dispatch(
            'playgroundgame_'.$game->getClassType(),
            array(
                'controller' => 'playgroundgame_'.$game->getClassType(),
                'action' => $game->firstStep(),
                'id' => $identifier,
                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
            )
        );
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
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute(
                            $game->getClassType().'/fangate',
                            array(
                                'id' => $game->getIdentifier(),
                                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                            )
                        )
                    );
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
            'isSubscribed'     => $isSubscribed,
            'isCtaActive'      => $isCtaActive,
        ));

        return $viewModel;
    }

    /**
      * leaderboardAction
      *
      * @return ViewModel $viewModel
      */
    public function leaderboardAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $p = $this->getEvent()->getRouteMatch()->getParam('p');

        $beforeLayout = $this->layout()->getTemplate();
        $subViewModel = $this->forward()->dispatch(
            'playgroundreward',
            array('action' => 'leaderboard', 'filter' => $filter, 'p' => $p)
        );
        
        // suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);

        // give the ability to the game to have its customized look and feel.
        $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();

        $templatePathResolver->addPath($l[0].'custom/'.$game->getIdentifier());

        return $subViewModel;
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

        $form = $sg->createFormFromJson($game->getPlayerForm()->getForm(), 'playerForm');

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            
            $form->setData($data);

            if ($form->isValid()) {
                // steps of the game
                $steps = $game->getStepsArray();
                // sub steps of the game
                $viewSteps = $game->getStepsViewsArray();

                // register position
                $key = array_search($this->params('action'), $viewSteps);
                if (!$key) {
                    // register is not a substep of the game so it's a step
                    $key = array_search($this->params('action'), $steps);
                    $keyStep = true;
                } else {
                    // register was a substep, i search the index of its parent
                    $key = array_search($key, $steps);
                    $keyStep = false;
                }

                // play position
                $keyplay = array_search('play', $viewSteps);

                if (!$keyplay) {
                    // play is not a substep, so it's a step
                    $keyplay = array_search('play', $steps);
                    $keyplayStep = true;
                } else {
                    // play is a substep so I search the index of its parent
                    $keyplay = array_search($keyplay, $steps);
                    $keyplayStep = false;
                }

                // If register step before play, I don't have no entry yet. I have to create one
                // If register after play step, I search for the last entry created by play step.

                if ($key < $keyplay || ($keyStep && !$keyplayStep && $key <= $keyplay)) {

                    $entry = $sg->play($game, $user);
                    if (!$entry) {
                        // the user has already taken part of this game and the participation limit has been reached
                        $this->flashMessenger()->addMessage('Vous avez déjà participé');
                    
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute(
                                $game->getClassType().'/result',
                                array(
                                    'id' => $identifier,
                                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                                )
                            )
                        );
                    }
                } else {
                    // I'm looking for an entry without anonymousIdentifier (the active entry in fact).
                    $entry = $sg->findLastEntry($game, $user);
                    if ($sg->hasReachedPlayLimit($game, $user)) {
                        // the user has already taken part of this game and the participation limit has been reached
                        $this->flashMessenger()->addMessage('Vous avez déjà participé');
                    
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute(
                                $game->getClassType().'/result',
                                array(
                                    'id' => $identifier,
                                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                                )
                            )
                        );
                    }
                }

                $sg->updateEntryPlayerForm($form->getData(), $game, $user, $entry);

                if(!empty($game->nextStep($this->params('action')))){
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute(
                            $game->getClassType() .'/' . $game->nextStep($this->params('action')),
                            array(
                                'id' => $game->getIdentifier(),
                                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                            ),
                            array('force_canonical' => true)
                        )
                    );
                }
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'form' => $form
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
            'user'           => $user,
            'availableGames' => $availableGames,
        ));

        return $viewModel;
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
                $view = $this->forward()->dispatch(
                    'playgrounduser_user',
                    array(
                        'controller' => 'playgrounduser_user',
                        'action' => 'registerFacebookUser',
                        'provider' => $channel
                    )
                );

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user) {
                    $redirectUrl = urlencode(
                        $this->frontendUrl()->fromRoute(
                            $game->getClassType() .'/play',
                            array(
                                'id' => $game->getIdentifier(),
                                'channel' => $channel
                            ),
                            array('force_canonical' => true)
                        )
                    );
                    $redirect =  $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute(
                            'zfcuser/register',
                            array('channel' => $channel)
                        ) . '?redirect='.$redirectUrl
                    );
                }
            }

            if ($game->getFbFan()) {
                if ($sg->checkIsFan($game) === false) {
                    $redirect =  $this->redirect()->toRoute(
                        $game->getClassType().'/fangate',
                        array('id' => $game->getIdentifier())
                    );
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
        if ($this->getRequest()->isXmlHttpRequest()) {
            $viewModel = new JsonModel();
            if ($game) {
                $view = $this->addAdditionalView($game);
                if ($view && $view instanceof \Zend\View\Model\ViewModel) {
                    $viewModel->setVariables($view->getVariables());
                }
            }
        } else {
            $viewModel = new ViewModel();

            if ($game) {
                $this->addMetaTitle($game);
                $this->addMetaBitly();
                $this->addGaEvent($game);

                $this->customizeGameDesign($game);
                
                // this is possible to create a specific game design in /design/frontend/default/custom.
                //It will precede all others templates.
                $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
                $l = $templatePathResolver->getPaths();
                $templatePathResolver->addPath($l[0].'custom/'.$game->getIdentifier());
                
                $view = $this->addAdditionalView($game);
                if ($view && $view instanceof \Zend\View\Model\ViewModel) {
                    $viewModel->addChild($view, 'additional');
                } elseif ($view && $view instanceof \Zend\Http\PhpEnvironment\Response) {
                    return $view;
                }

                $this->layout()->setVariables(
                    array(
                        'action' => $this->params('action'),
                        'game' => $game,
                        'flashMessages'    => $this->flashMessenger()->getMessages(),
                        'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                    )
                );
            }
        }
        
        if ($game) {
            $viewModel->setVariables($this->getShareData($game));
            $viewModel->setVariables(array('game' => $game));
        }

        return $viewModel;
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function addAdditionalView($game)
    {
        $view = false;

        $actionName = $this->getEvent()->getRouteMatch()->getParam('action', 'not-found');
        $stepsViews = json_decode($game->getStepsViews(), true);
        if ($stepsViews && isset($stepsViews[$actionName])) {
            $beforeLayout = $this->layout()->getTemplate();
            $actionData = $stepsViews[$actionName];
            if (is_string($actionData)) {
                $action = $actionData;
                $controller = $this->getEvent()->getRouteMatch()->getParam('controller', 'playgroundgame_game');
                $view = $this->forward()->dispatch(
                    $controller,
                    array(
                        'action' => $action,
                        'id' => $game->getIdentifier()
                    )
                );
            } elseif (is_array($actionData) && count($actionData)>0) {
                $action = key($actionData);
                $controller = $actionData[$action];
                $view = $this->forward()->dispatch(
                    $controller,
                    array(
                        'action' => $action,
                        'id' => $game->getIdentifier()
                    )
                );
            }
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

    /**
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function addGaEvent($game)
    {
        // Google Analytics event
        $ga = $this->getServiceLocator()->get('google-analytics');
        $event = new \PlaygroundCore\Analytics\Event($game->getClassType(), $this->params('action'));
        $event->setLabel($game->getTitle());
        $ga->addEvent($event);
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function addMetaTitle($game)
    {
        $sg = $this->getGameService();
        $title = $this->translate($game->getTitle());
        $sg->getServiceManager()->get('ViewHelperManager')->get('HeadTitle')->set($title);
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

    /**
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function customizeGameDesign($game)
    {
        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet(
                $this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet()
            );
        }
    }

    /**
     * @param \PlaygroundGame\Entity\Game $game
     */
    public function getShareData($game)
    {
        $fo = $this->getServiceLocator()->get('facebook-opengraph');
        // I change the fbappid if i'm in fb
        if ($this->getEvent()->getRouteMatch()->getParam('channel') === 'facebook') {
            $fo->setId($game->getFbAppId());
        }

        // If I want to add a share block in my view
        if ($game->getFbShareMessage()) {
            $fbShareMessage = $game->getFbShareMessage();
        } else {
            $fbShareMessage = str_replace(
                '__placeholder__',
                $game->getTitle(),
                $this->getOptions()->getDefaultShareMessage()
            );
        }

        if ($game->getFbShareImage()) {
            $fbShareImage = $this->frontendUrl()->fromRoute(
                '',
                array('channel' => ''),
                array('force_canonical' => true),
                false
            ) . $game->getFbShareImage();
        } else {
            $fbShareImage = $this->frontendUrl()->fromRoute(
                '',
                array('channel' => ''),
                array('force_canonical' => true),
                false
            ) . $game->getMainImage();
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));

        // Without bit.ly shortener
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            $game->getClassType(),
            array(
                'id' => $game->getIdentifier(),
                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
            ),
            array('force_canonical' => true)
        );
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        // FB Requests only work when it's a FB app
        if ($game->getFbRequestMessage()) {
            $fbRequestMessage = urlencode($game->getFbRequestMessage());
        } else {
            $fbRequestMessage = str_replace(
                '__placeholder__',
                $game->getTitle(),
                $this->getOptions()->getDefaultShareMessage()
            );
        }

        if ($game->getTwShareMessage()) {
            $twShareMessage = $game->getTwShareMessage() . $socialLinkUrl;
        } else {
            $twShareMessage = str_replace(
                '__placeholder__',
                $game->getTitle(),
                $this->getOptions()->getDefaultShareMessage()
            ) . $socialLinkUrl;
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

        if (count($game->getPrizes()) == 0) {
            return $this->notFoundAction();
        }

        $viewModel = $this->buildView($game);

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
        $viewModel->setVariables(array('prize'=> $prize));

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

        $games = $this->getGameService()->getActiveGames(false, '', 'endDate');
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

        $viewModel = $this->buildView($game);

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
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array(
                        'id' => $identifier,
                        'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                    )
                )
            );
        }
    
        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode(
                $this->frontendUrl()->fromRoute(
                    'postvote/result',
                    array(
                        'id' => $game->getIdentifier(),
                        'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                    )
                )
            );
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'zfcuser/register',
                    array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))
                ) . '?redirect='.$redirect
            );
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
    
        $this->getGameService()->sendMail($game, $user, $lastEntry);
    
        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
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

    public function optinAction()
    {
        $request = $this->getRequest();
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $userService = $this->getServiceLocator()->get('zfcuser_user_service');
    
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        if ($request->isPost()) {
            $data['optin'] = ($this->params()->fromPost('optin'))? 1:0;
            $data['optinPartner'] = ($this->params()->fromPost('optinPartner'))? 1:0;

            $userService->updateNewsletter($data);
        }

        return $this->redirect()->toUrl(
            $this->frontendUrl()->fromRoute(
                'frontend/' . $game->getClassType() . '/index',
                array(
                    'id' => $game->getIdentifier(),
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                )
            )
        );
    }
    
    public function loginAction()
    {
        $request = $this->getRequest();
        $form    = $this->getServiceLocator()->get('zfcuser_login_form');
        
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        
        $sg = $this->getGameService();
        
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }
    
        if ($request->isPost()) {
            $form->setData($request->getPost());
            
            if (!$form->isValid()) {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage(
                    'Authentication failed. Please try again.'
                );
                

                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType() . '/login',
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
            }
            
            // clear adapters
            $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
            $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

            $logged = $this->forward()->dispatch('playgrounduser_user', array('action' => 'ajaxauthenticate'));

            if ($logged) {
                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType() . '/' . $game->nextStep('index'),
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
            } else {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage(
                    'Authentication failed. Please try again.'
                );
                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType() . '/login',
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
            }
        }
        
        $form->setAttribute(
            'action',
            $this->frontendUrl()->fromRoute(
                $game->getClassType().'/login',
                array(
                    'id' => $game->getIdentifier(),
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                )
            )
        );
        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'form' => $form,
        ));
        return $viewModel;
    }

    public function userregisterAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();
        $game = $sg->checkGame($identifier, false);
        $userOptions = $this->getServiceLocator()->get('zfcuser_module_options');

        if ($this->zfcUserAuthentication()->hasIdentity()) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    $game->getClassType().'/'.$game->nextStep('index'),
                    array(
                        'id' => $game->getIdentifier(),
                        'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                    )
                )
            );
        }
        $request = $this->getRequest();
        $service = $this->getServiceLocator()->get('zfcuser_user_service');
        $form = $this->getServiceLocator()->get('playgroundgame_register_form');
        $socialnetwork = $this->params()->fromRoute('socialnetwork', false);
        $form->setAttribute(
            'action',
            $this->frontendUrl()->fromRoute(
                $game->getClassType().'/user-register',
                array(
                    'id' => $game->getIdentifier(),
                    'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                )
            )
        );
        $params = array();
        $socialCredentials = array();

        if ($userOptions->getUseRedirectParameterIfPresent() && $request->getQuery()->get('redirect')) {
            $redirect = $request->getQuery()->get('redirect');
        } else {
            $redirect = false;
        }

        if ($socialnetwork) {
            $infoMe = $this->getProviderService()->getInfoMe($socialnetwork);

            if (!empty($infoMe)) {
                $user = $this->getProviderService()->getUserProviderMapper()->findUserByProviderId(
                    $infoMe->identifier,
                    $socialnetwork
                );

                if ($user || $service->getOptions()->getCreateUserAutoSocial() === true) {
                    //on le dirige vers l'action d'authentification
                    if (! $redirect && $userOptions->getLoginRedirectRoute() != '') {
                        $redirect = $this->frontendUrl()->fromRoute(
                            $game->getClassType().'/login',
                            array(
                                'id' => $game->getIdentifier(),
                                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                            )
                        );
                    }
                    $redir = $this->frontendUrl()->fromRoute(
                        $game->getClassType().'/login',
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    ) .'/' . $socialnetwork . ($redirect ? '?redirect=' . $redirect : '');

                    return $this->redirect()->toUrl($redir);
                }

                // Je retire la saisie du login/mdp
                $form->setAttribute(
                    'action',
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType().'/user-register',
                        array(
                            'id' => $game->getIdentifier(),
                            'socialnetwork' => $socialnetwork,
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
                $form->remove('password');
                $form->remove('passwordVerify');

                $birthMonth = $infoMe->birthMonth;
                if (strlen($birthMonth) <= 1) {
                    $birthMonth = '0'.$birthMonth;
                }
                $birthDay = $infoMe->birthDay;
                if (strlen($birthDay) <= 1) {
                    $birthDay = '0'.$birthDay;
                }

                $gender = $infoMe->gender;
                if ($gender == 'female') {
                    $title = 'Me';
                } else {
                    $title = 'M';
                }

                $params = array(
                    //'birth_year'  => $infoMe->birthYear,
                    'title'      => $title,
                    'dob'      => $birthDay.'/'.$birthMonth.'/'.$infoMe->birthYear,
                    'firstname'   => $infoMe->firstName,
                    'lastname'    => $infoMe->lastName,
                    'email'       => $infoMe->email,
                    'postalCode' => $infoMe->zip,
                );
                $socialCredentials = array(
                    'socialNetwork' => strtolower($socialnetwork),
                    'socialId'      => $infoMe->identifier,
                );
            }
        }

        $redirectUrl = $this->frontendUrl()->fromRoute(
            $game->getClassType().'/user-register',
            array(
                'id' => $game->getIdentifier(),
                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
            )
        ) .($socialnetwork ? '/' . $socialnetwork : ''). ($redirect ? '?redirect=' . $redirect : '');
        $prg = $this->prg($redirectUrl, true);

        if ($prg instanceof Response) {
            return $prg;
        } elseif ($prg === false) {
            $form->setData($params);
            $viewModel = $this->buildView($game);
            $viewModel->setVariables(array(
                'registerForm' => $form,
                'enableRegistration' => $userOptions->getEnableRegistration(),
                'redirect' => $redirect,
            ));
            return $viewModel;
        }

        $post = $prg;
        $post = array_merge(
            $post,
            $socialCredentials
        );

        $user = $service->register($post, 'playgroundgame_register_form');

        if (! $user) {
            $viewModel = $this->buildView($game);
            $viewModel->setVariables(array(
                'registerForm' => $form,
                'enableRegistration' => $userOptions->getEnableRegistration(),
                'redirect' => $redirect,
            ));
            
            return $viewModel;
        }

        if ($service->getOptions()->getEmailVerification()) {
            $vm = new ViewModel(array('userEmail' => $user->getEmail()));
            $vm->setTemplate('playground-user/register/registermail');

            return $vm;
        } elseif ($service->getOptions()->getLoginAfterRegistration()) {
            $identityFields = $service->getOptions()->getAuthIdentityFields();
            if (in_array('email', $identityFields)) {
                $post['identity'] = $user->getEmail();
            } elseif (in_array('username', $identityFields)) {
                $post['identity'] = $user->getUsername();
            }
            $post['credential'] = isset($post['password'])?$post['password']:'';
            $request->setPost(new Parameters($post));

            // clear adapters
            $this->zfcUserAuthentication()->getAuthAdapter()->resetAdapters();
            $this->zfcUserAuthentication()->getAuthService()->clearIdentity();

            $logged = $this->forward()->dispatch('playgrounduser_user', array('action' => 'ajaxauthenticate'));

            if ($logged) {
                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType() . '/' . $game->nextStep('index'),
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
            } else {
                $this->flashMessenger()->setNamespace('zfcuser-login-form')->addMessage(
                    'Authentication failed. Please try again.'
                );
                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        $game->getClassType() . '/login',
                        array(
                            'id' => $game->getIdentifier(),
                            'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
                        )
                    )
                );
            }
        }

        $redirect = $this->frontendUrl()->fromRoute(
            $game->getClassType().'/login',
            array(
                'id' => $game->getIdentifier(),
                'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')
            )
        ) . ($socialnetwork ? '/' . $socialnetwork : ''). ($redirect ? '?redirect=' . $redirect : '');

        return $this->redirect()->toUrl($redirect);
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
}
