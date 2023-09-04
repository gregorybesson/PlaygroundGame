<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Service\GameService;
use PlaygroundGame\Service\Prize as PrizeService;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\Container;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;

class GameController extends AbstractActionController
{
  /**
   * @var \PlaygroundGame\Service\GameService
   */
  protected $gameService;

  protected $prizeService;

  protected $options;

  protected $game;

  protected $user;

  protected $withGame = array(
    'home',
    'index',
    'terms',
    'conditions',
    'leaderboard',
    'register',
    'bounce',
    'createTeam',
    'inviteToTeam',
    'prizes',
    'prize',
    'share',
    'optin',
    'terms-optin',
    'login',
    'logout',
    'ajaxforgot',
    'play',
    'result',
    'preview',
    'list',
    'comments',
  );

  protected $withOnlineGame = array(
    'leaderboard',
    'register',
    'bounce',
    'play',
    'result',
  );

  /**
   * These steps are available only when the game is started
   */
  protected $withStartedGame = array(
    'preview',
    'createTeam',
    'inviteToTeam',
    'register',
    'play',
  );

  protected $withAnyUser = array(
    'share',
    'result',
    'play',
    'logout',
    'createTeam',
    'inviteToTeam',
  );

  protected $isSoloGame = false;

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

  public function setEventManager(\Laminas\EventManager\EventManagerInterface $events)
  {
    parent::setEventManager($events);

    $controller = $this;

    $events->attach('dispatch', function (\Laminas\Mvc\MvcEvent $e) use ($controller) {

      $session = new Container('facebook');
      $identifier = $e->getRouteMatch()->getParam('id');

      // I set the right identifier if I come from FB
      // From FB, the games have all the same identifier. I use the FB Page Id to find the right game
      // So for 1 FB page, you can have only 1 game ;/
      if ($identifier === 'facebook' && $session->offsetExists('signed_request')) {
        $sr = $session->offsetGet('signed_request');
        $identifier = $controller->getGameService()->getGameIdentifierFromFacebook($sr['page']['id']);
      }

      $controller->game = $controller->getGameService()->checkGame($identifier, false);

      if (
        !$controller->game
        && in_array($controller->params('action'), $controller->withGame)
      ) {
        return $controller->notFoundAction();
      }

      if (
        $controller->game
        && $controller->game->isClosed()
        && in_array($controller->params('action'), $controller->withOnlineGame)
      ) {
        return $controller->notFoundAction();
      }

      $config = $this->getServiceLocator()->get('config');
      $customUrl = str_replace('frontend.', '', $e->getRouteMatch()->getMatchedRouteName());
      $customUrl = explode("/", $customUrl)[0];

      if (
        isset($config['custom_games'])
        && $controller->game !== false
        && isset($config['custom_games'][$controller->game->getIdentifier()])
        && $controller->getRequest()->getUri()->getHost() === $customUrl
      ) {
        $this->isSoloGame = true;
      }

      if ($controller->game) {
        // this is possible to create a specific game design in /design/frontend/default/custom.
        //It will precede all others templates.
        $templatePathResolver = $controller->getServiceLocator()->get('Laminas\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        $templatePathResolver->addPath($l[0] . 'custom/' . $controller->game->getIdentifier());
      }

      $controller->user = $controller->lmcUserAuthentication()->getIdentity();
      if (
        $controller->game
        && !$controller->user
        && !$controller->game->getAnonymousAllowed()
        && in_array($controller->params('action'), $controller->withAnyUser)
      ) {
        // if the current game runs under Facebook
        if ($session->offsetExists('signed_request')) {
          $session->offsetSet('fb-redirect', $controller->getRequest()->getUri());
          // Let's start the FB registration dance !
          $controller->forward()->dispatch(
            'playgrounduser_user',
            array(
              'controller' => 'playgrounduser_user',
              'action'     => 'registerFacebookUser',
              'provider'   => 'facebook',
            )
          );
        }
        $redirect = urlencode(
          $controller->url()->fromRoute(
            'frontend/' . $controller->game->getClassType() . '/' . $controller->params('action'),
            array('id'              => $controller->game->getIdentifier()),
            array('force_canonical' => true)
          )
        );

        if ($this->isSoloGame) {
          $urlRegister = $controller->url()->fromRoute(
            'frontend.' . $customUrl . '/' . $controller->game->getClassType() . '/user-register',
            array(),
            array('force_canonical' => true)
          ) . '?redirect=' . $redirect;
        } else {
          $urlRegister = $controller->url()->fromRoute(
            'frontend/lmcuser/register',
            array(),
            array('force_canonical' => true)
          ) . '?redirect=' . $redirect;
        }

        // code permettant d'identifier un custom game
        // ligne $config = $controller->getGameService()->getServiceManager()->get('config');
        // ligne $customUrl = str_replace('frontend.', '', $e->getRouteMatch()->getParam('area', ''));
        // ligne if ($config['custom_games'][$controller->game->getIdentifier()] &&
        // ligne    $controller->getRequest()->getUri()->getHost() === $customUrl
        // ligne ) {
        return $controller->redirect()->toUrl($urlRegister);
      }

      // If the game is finished, I redirect some actions to result (you can't play it anymore)
      if (
        $controller->game
        && $controller->game->isFinished()
        && in_array($controller->params('action'), $controller->withStartedGame)
      ) {
        if ($this->isSoloGame) {
          $urlResult = $controller->url()->fromRoute(
            'frontend.' . $customUrl . '/' . $controller->game->getClassType() . '/result',
            array('id' => $controller->game->getIdentifier()),
            array('force_canonical' => true)
          );
        } else {
          $urlResult = $controller->url()->fromRoute(
            'frontend/' . $controller->game->getClassType() . '/result',
            array('id' => $controller->game->getIdentifier()),
            array('force_canonical' => true)
          );
        }
        return $controller->redirect()->toUrl($urlResult);
      }

      return;
    }, 100); // execute before executing action logic
  }

  /**
   * Action called if matched action does not exist
   * For this view not to be catched by Laminas\Mvc\View\RouteNotFoundStrategy
   * it has to be rendered in the controller. Hence the code below.
   *
   * This action is injected as a catchall action for each custom_games definition
   * This way, when a custom_game is created, the 404 is it's responsability and the
   * view can be defined in design/frontend/default/custom/$slug/playground_game/$gametype/404.phtml
   *
   *
   * @return \Laminas\Stdlib\ResponseInterface
   */
  public function notFoundAction()
  {
    $templatePathResolver = $this->getServiceLocator()->get('Laminas\View\Resolver\TemplatePathStack');

    // I create a template path in which I can find a custom template
    $controller     = explode('\\', get_class($this));
    $controllerPath = str_replace('Controller', '', end($controller));
    $controllerPath = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $controllerPath));
    $uri            = $this->getRequest()->getUri()->getPath();
    if ($this->game) {
      $uri = str_replace($controllerPath . '/' . $this->game->getIdentifier() . '/', '', $uri);
    }
    $uri      = str_replace("/" . $this->getEvent()->getRouteMatch()->getParam('locale') . "/", "/", $uri);
    $template = 'playground-game/' . $controllerPath . '/custom' . $uri;

    $template = (substr($template, -strlen('.html')) === '.html') ? $template : $template . '.phtml';
    if (false === $templatePathResolver->resolve($template)) {
      $viewRender = $this->getServiceLocator()->get('ViewRenderer');

      $this->getEvent()->getRouteMatch()->setParam('action', 'not-found');
      $this->response->setStatusCode(404);

      $res = 'error/404';

      $viewModel = $this->buildView($this->game);
      $viewModel->setTemplate($res);

      $this->layout()->setVariable("content", $viewRender->render($viewModel));
      $this->response->setContent($viewRender->render($this->layout()));

      return $this->response;
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setTemplate($template);

    return $viewModel;
  }

  /**
   * This action acts as a hub : Depending on the first step of the game, it will forward the action to this step
   */
  public function homeAction()
  {
    // This fix exists only for safari in FB on Windows : we need to redirect the user to the page
    // outside of iframe for the cookie to be accepted. PlaygroundCore redirects to the FB Iframed page when
    // it discovers that the user arrives for the first time on the game in FB.
    // When core redirects, it adds a 'redir_fb_page_id' var in the querystring
    // Here, we test if this var exist, and then send the user back to the game in FB.
    // Now the cookie will be accepted by Safari...
    $pageId = $this->params()->fromQuery('redir_fb_page_id');
    if (!empty($pageId)) {
      $appId = 'app_' . $this->game->getFbAppId();
      $url   = '//www.facebook.com/pages/game/' . $pageId . '?sk=' . $appId;

      return $this->redirect()->toUrl($url);
    }

    // If an entry has already been done during this session, I reset the anonymous_identifier cookie
    // so that another person can play the same game (if game conditions are fullfilled)
    // or the same person can play another game
    $session = new Container('anonymous_identifier');
    if ($session->offsetExists('anonymous_identifier')) {
      $session->offsetUnset('anonymous_identifier');
    }

    $classGame = __NAMESPACE__ . '\\' . ucfirst($this->game->getClassType());
    return $this->forward()->dispatch(
      $classGame,
      array(
        'controller' => $classGame,
        'action'     => $this->game->firstStep(),
        'id'         => $this->game->getIdentifier()
      )
    );
  }

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

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array(
      'isSubscribed' => $isSubscribed,
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
    $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
    $p      = $this->getEvent()->getRouteMatch()->getParam('p');

    $beforeLayout = $this->layout()->getTemplate();
    $subViewModel = $this->forward()->dispatch(
      'playgroundreward',
      array('action' => 'leaderboard', 'filter' => $filter, 'p' => $p)
    );

    // suite au forward, le template de layout a changé, je dois le rétablir...
    $this->layout()->setTemplate($beforeLayout);
    $this->layout()->setVariables(
      array(
        'action' => $this->params('action'),
        'game'   => $this->game,
      )
    );

    $subViewModel->setVariables($this->getShareData($this->game));
    $subViewModel->setVariables(array('game' => $this->game, 'user' => $this->user));

    return $subViewModel;
  }

  /**
   * This action has been designed to be called by other controllers
   * It gives the ability to display an information form and persist it in the game entry
   *
   * @return \Laminas\View\Model\ViewModel
   */
  public function registerAction()
  {
    $formDef = $this->game->getPlayerForm();
    if ($formDef !== null) {
      $form = $this->getGameService()->createFormFromJson($formDef->getForm(), 'playerForm');
    } else {
      return $this->notFoundAction();
    }

    if ($this->getRequest()->isPost()) {
      // POST Request: Process form
      $data = array_merge_recursive(
        $this->getRequest()->getPost()->toArray(),
        $this->getRequest()->getFiles()->toArray()
      );

      $form->setData($data);

      if ($form->isValid()) {
        // steps of the game
        $steps = $this->game->getStepsArray();
        // sub steps of the game
        $viewSteps = $this->game->getStepsViewsArray();
        $keyStep = false;

        // register position
        $key = array_search($this->params('action'), $viewSteps);
        if ($key === false) {
          // register is not a substep of the game so it's a step
          $key     = array_search($this->params('action'), $steps);
          if ($key !== false) {
            $keyStep = true;
          } else {
            $key = -1;
          }
        } else {
          // register was a substep, I search the index of its parent
          $key     = array_search($key, $steps);
        }

        // play position
        $keyplay = array_search('play', $viewSteps);

        if (!$keyplay) {
          // play is not a substep, so it's a step
          $keyplay     = array_search('play', $steps);
          $keyplayStep = true;
        } else {
          // play is a substep so I search the index of its parent
          $keyplay     = array_search($keyplay, $steps);
          $keyplayStep = false;
        }

        // If register step before play, I don't have no entry yet. I have to create one
        // If register after play step, I search for the last entry created by play step.
        if ($key < $keyplay || ($keyStep && !$keyplayStep && $key <= $keyplay)) {
          // I need to check if this anonymous user has already played. This one is transmitted through
          // a cookie... so I need to fill in this cookie if it's not already updated.
          if (
            $this->game->getAnonymousAllowed() &&
            $this->game->getAnonymousIdentifier() &&
            isset($data[$this->game->getAnonymousIdentifier()])
          ) {
            $session = new \Laminas\Session\Container('anonymous_identifier');
            if (empty($session->offsetGet('anonymous_identifier'))) {
              $anonymousIdentifier = $data[$this->game->getAnonymousIdentifier()];

              // I must transmit this info during the whole game workflow
              $session->offsetSet('anonymous_identifier', $anonymousIdentifier);
            }
          }
          $playError = null;
          $entry = $this->getGameService()->play($this->game, $this->user, $playError);
          if (!$entry) {
            $reason = "";
            if ($playError === -1) {
              // the user has already taken part to this game and the participation limit has been reached
              $this->flashMessenger()->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate("You have already played", "playgroundgame"));
              $reason = '?playLimitReached=1';
              $noEntryRedirect = $this->frontendUrl()->fromRoute(
                $this->game->getClassType() . '/result',
                array(
                  'id' => $this->game->getIdentifier(),
                )
              ) . $reason;
            } elseif ($playError === -2) {
              // the user has not accepted the mandatory rules of the game
              $this->flashMessenger()->addMessage('Vous devez accepter le réglement');
              $reason = '?NoOptin=1';
              $noEntryRedirect = $this->frontendUrl()->fromRoute(
                $this->game->getClassType(),
                array(
                  'id' => $this->game->getIdentifier(),
                )
              ) . $reason;
            } elseif ($playError === -3) {
              // the user has enough points to buy an entry to this game
              $this->flashMessenger()->addMessage("Vous ne pouvez pas acheter la partie");
              $reason = '?NotPaid=1';
              $noEntryRedirect = $this->frontendUrl()->fromRoute(
                $this->game->getClassType(),
                array(
                  'id' => $this->game->getIdentifier(),
                )
              ) . $reason;
            }

            return $this->redirect()->toUrl($noEntryRedirect);
          }
        } else {
          // I'm looking for an entry without anonymousIdentifier (the active entry in fact).
          $entry = $this->getGameService()->findLastEntry($this->game, $this->user);
          if ($this->getGameService()->hasReachedPlayLimit($this->game, $this->user)) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate("You have already played", "playgroundgame"));

            return $this->redirect()->toUrl(
              $this->frontendUrl()->fromRoute(
                $this->game->getClassType() . '/result',
                array(
                  'id' => $this->game->getIdentifier(),
                )
              )
            );
          }
        }

        $this->getGameService()->updateEntryPlayerForm($form->getData(), $this->game, $this->user, $entry);

        if (!empty($this->game->nextStep($this->params('action')))) {
          return $this->redirect()->toUrl(
            $this->frontendUrl()->fromRoute(
              $this->game->getClassType() . '/' . $this->game->nextStep($this->params('action')),
              array('id'              => $this->game->getIdentifier()),
              array('force_canonical' => true)
            )
          );
        }
      }
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(
      array(
        'form' => $form,
      )
    );

    return $viewModel;
  }

  /**
   * This action takes care of the terms of the game
   */
  public function termsAction()
  {
    $viewModel = $this->buildView($this->game);

    return $viewModel;
  }

  /**
   * This action takes care of the conditions of the game
   */
  public function conditionsAction()
  {
    $viewModel = $this->buildView($this->game);

    return $viewModel;
  }

  /**
   * This action takes care of bounce page of the game
   */
  public function bounceAction()
  {
    $availableGames = $this->getGameService()->getAvailableGames($this->user);

    $rssUrl = '';
    $config = $this->getGameService()->getServiceManager()->get('config');
    if (isset($config['rss']['url'])) {
      $rssUrl = $config['rss']['url'];
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array(
      'rssUrl'         => $rssUrl,
      'user'           => $this->user,
      'availableGames' => $availableGames,
    ));

    return $viewModel;
  }

  /**
   * This action displays the Prizes page associated to the game
   */
  public function prizesAction()
  {
    if (count($this->game->getPrizes()) == 0) {
      return $this->notFoundAction();
    }

    $viewModel = $this->buildView($this->game);

    return $viewModel;
  }

  /**
   * This action displays a specific Prize page among those associated to the game
   */
  public function prizeAction()
  {
    $prizeIdentifier = $this->getEvent()->getRouteMatch()->getParam('prize');
    $prize           = $this->getPrizeService()->getPrizeMapper()->findByIdentifier($prizeIdentifier);

    if (!$prize) {
      return $this->notFoundAction();
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array('prize' => $prize));

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

    $games = $this->getGameService()->getActiveGames(false, '', 'endDate', 'ASC');
    if (is_array($games)) {
      $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($games));
    } else {
      $paginator = $games;
    }

    $paginator->setItemCountPerPage(7);
    $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

    $bitlyclient = $this->getOptions()->getBitlyUrl();
    $bitlyuser   = $this->getOptions()->getBitlyUsername();
    $bitlykey    = $this->getOptions()->getBitlyApiKey();

    $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
    $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
    $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

    $this->layout()->setVariables(
      array(
        'sliderItems'  => $sliderItems,
        'currentPage'  => array(
          'pageGames'   => 'games',
          'pageWinners' => '',
        ),
      )
    );

    return new ViewModel(
      array(
        'games' => $paginator,
      )
    );
  }

  public function shareAction()
  {
    $viewModel = new JsonModel();
    $viewModel->setTerminal(true);
    $statusMail = null;
    $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);

    $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
    $form->setAttribute('method', 'post');

    // buildView must be before sendMail because it adds the game template path to the templateStack
    //$viewModel = $this->buildView($this->game);

    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost()->toArray();
      $form->setData($data);
      if ($form->isValid()) {
        $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, $lastEntry);
        if ($result) {
          $statusMail = true;
        }
      }
    }

    $viewModel->setVariables([
      'statusMail' => $statusMail,
    ]);

    return $viewModel;
  }

  public function joinTeamAction()
  {
    $result = false;
    $team = null;

    // If user has been invited to a team, I add this user to the team
    if ($this->lmcUserAuthentication()->hasIdentity()) {
      $user = $this->lmcUserAuthentication()->getIdentity();
      $invitationMapper = $this->getServiceLocator()->get('playgroundgame_invitation_mapper');
      $invitation = $invitationMapper->findOneBy(array('requestKey' => $user->getEmail()));
      if ($invitation) {
        $team = $invitation->getHost()->getTeams()->first();
        $members = $team->getUsers();
        $found = false;
        foreach ($members as $member) {
          if ($member->getId() === $user->getId()) {
            $found = true;
          }
        }
        if (!$found) {
          $teamMapper = $this->getServiceLocator()->get('playgrounduser_team_mapper');
          $team->addUser($user);
          $teamMapper->update($team);
        }

        $result = true;
      }
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables([
      'team'       => $team,
      'result'     => $result,
    ]);

    return $viewModel;
  }

  public function inviteToTeamAction()
  {

    if (count($this->user->getTeams()) == 0) {
      return $this->redirect()->toUrl(
        $this->frontendUrl()->fromRoute(
          $this->game->getClassType() . '/create-team',
          array('id' => $this->game->getIdentifier())
        )
      );
    }

    $team = $this->user->getTeams()->first();
    $invitationMapper = $this->getServiceLocator()->get('playgroundgame_invitation_mapper');
    $invitations = $invitationMapper->findBy(array('host' => $this->user, 'game' => $this->game));
    $statusMail = null;
    $message = '';
    $isHost = ($this->user->getId() === $team->getHost()->getId()) ? true : false;

    $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
    $form->setAttribute('method', 'post');

    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost()->toArray();
      $form->setData($data);
      if ($form->isValid()) {
        $result = $this->getGameService()->inviteToTeam($data, $this->game, $this->user);
        if ($result['result']) {
          $statusMail = true;
        } else {
          $statusMail = false;
          $message    = $result['message'];
        }
      }
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array(
      'team'       => $team,
      'invitations' => $invitations,
      'message'    => $message,
      'statusMail' => $statusMail,
      'form'       => $form,
      'isHost'     => $isHost,
    ));

    return $viewModel;
  }

  public function createTeamAction()
  {
    if (count($this->user->getTeams()) > 0) {
      return $this->redirect()->toUrl(
        $this->frontendUrl()->fromRoute(
          $this->game->getClassType() . '/invite-to-team',
          array('id' => $this->game->getIdentifier())
        )
      );
    }

    $form = $this->getServiceLocator()->get('playgroundgame_createteam_form');
    $form->setAttribute('method', 'post');

    if ($this->getRequest()->isPost()) {
      $data = $this->getRequest()->getPost()->toArray();
      $form->setData($data);
      if ($form->isValid()) {
        $teamMapper = $this->getServiceLocator()->get('playgrounduser_team_mapper');
        $users = new \Doctrine\Common\Collections\ArrayCollection();
        $users->add($this->user);
        $teamName = $data['name'];
        $slugify = new \PlaygroundCore\Filter\Slugify;

        $team = new \PlaygroundUser\Entity\Team();
        $team->setName($teamName);
        $team->setIdentifier($slugify($teamName));
        $team->setHost($this->user);
        $team->addUsers($users);

        $teamMapper->insert($team);

        return $this->redirect()->toUrl(
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/invite-to-team',
            array('id' => $this->game->getIdentifier())
          )
        );
      }
    }

    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array(
      'form'       => $form,
    ));

    return $viewModel;
  }

  public function fbshareAction()
  {
    $viewModel = new JsonModel();
    $viewModel->setTerminal(true);
    $fbId = $this->params()->fromQuery('fbId');
    if (!$this->game) {
      return $this->errorJson();
    }
    $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
    if (!$entry) {
      return $this->errorJson();
    }
    if (!$fbId) {
      return $this->errorJson();
    }

    $this->getGameService()->postFbWall($fbId, $this->game, $this->user, $entry);

    return $this->successJson();
  }

  public function fbrequestAction()
  {
    $viewModel = new ViewModel();
    $viewModel->setTerminal(true);
    $fbId = $this->params()->fromQuery('fbId');
    $to   = $this->params()->fromQuery('to');

    if (!$this->game) {
      return $this->errorJson();
    }
    $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
    if (!$entry) {
      return $this->errorJson();
    }
    if (!$fbId) {
      return $this->errorJson();
    }

    $this->getGameService()->postFbRequest($fbId, $this->game, $this->user, $entry, $to);

    return $this->successJson();
  }

  public function tweetAction()
  {
    $tweetId = $this->params()->fromQuery('tweetId');

    if (!$this->game) {
      return $this->errorJson();
    }
    $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
    if (!$entry) {
      return $this->errorJson();
    }
    if (!$tweetId) {
      return $this->errorJson();
    }

    $this->getGameService()->postTwitter($tweetId, $this->game, $this->user, $entry);

    return $this->successJson();
  }

  public function googleAction()
  {
    $viewModel = new ViewModel();
    $viewModel->setTerminal(true);
    $googleId = $this->params()->fromQuery('googleId');

    if (!$this->game) {
      return $this->errorJson();
    }
    $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
    if (!$entry) {
      return $this->errorJson();
    }
    if (!$googleId) {
      return $this->errorJson();
    }

    $this->getGameService()->postGoogle($googleId, $this->game, $this->user, $entry);

    return $this->successJson();
  }

  public function optinAction()
  {
    $userService = $this->getServiceLocator()->get('lmcuser_user_service');

    if ($this->getRequest()->isPost()) {
      $data['optin']        = ($this->params()->fromPost('optin')) ? 1 : 0;
      $data['optinPartner'] = ($this->params()->fromPost('optinPartner')) ? 1 : 0;

      $userService->updateNewsletter($data);
    }

    return $this->redirect()->toUrl(
      $this->frontendUrl()->fromRoute(
        $this->game->getClassType(),
        array('id' => $this->game->getIdentifier())
      )
    );
  }

  public function termsOptinAction()
  {
    $viewModel = $this->buildView($this->game);

    if ($this->getRequest()->isPost()) {
      $optin = $this->params()->fromPost('termsOptin');
      $result = $this->getGameService()->setTermsOptin($optin, $this->game, $this->user);
      $viewModel->setVariables(
        ['success' => $result]
      );
    }

    return $viewModel;
  }

  public function loginAction()
  {
    $request = $this->getRequest();
    $redirect = "";
    if ($request->getQuery()->get('redirect') != '') {
      $redirect = $request->getQuery()->get('redirect');
    }
    $form    = $this->getServiceLocator()->get('lmcuser_login_form');

    if ($request->isPost()) {
      $form->setData($request->getPost());

      if (!$form->isValid()) {
        $this->flashMessenger()->addMessage(
          'Authentication failed. Please try again.'
        );

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
          'form'          => $form,
          'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
      }

      // clear adapters
      $this->lmcUserAuthentication()->getAuthAdapter()->resetAdapters();
      $this->lmcUserAuthentication()->getAuthService()->clearIdentity();

      $logged = $this->forward()->dispatch('playgrounduser_user', array('action' => 'ajaxauthenticate'));

      if (!$logged) {
        $this->flashMessenger()->addMessage(
          'Authentication failed. Please try again.'
        );

        return $this->redirect()->toUrl(
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/login',
            array('id' => $this->game->getIdentifier())
          )
        );
      } else {
        if ($redirect != "") {
          return $this->redirect()->toUrl($redirect);
        } else {
          return $this->redirect()->toUrl(
            $this->frontendUrl()->fromRoute(
              $this->game->getClassType() . '/' . $this->game->nextStep('index'),
              array('id' => $this->game->getIdentifier())
            )
          );
        }
      }
    }

    $form->setAttribute(
      'action',
      $this->frontendUrl()->fromRoute(
        $this->game->getClassType() . '/login',
        array('id' => $this->game->getIdentifier())
      )
    );
    $viewModel = $this->buildView($this->game);
    $viewModel->setVariables(array(
      'form'          => $form,
      'flashMessages' => $this->flashMessenger()->getMessages(),
    ));
    return $viewModel;
  }

  public function logoutAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgrounduser_user',
      array(
        'controller' => 'playgrounduser_user',
        'action'     => 'logout',
        'id'         => $this->game->getIdentifier()
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game'      => $this->game));
    }

    return $viewModel;
  }

  public function userregisterAction()
  {
    $pguserOptions = $this->getServiceLocator()->get('playgrounduser_module_options');
    $userOptions = $this->getServiceLocator()->get('lmcuser_module_options');

    if ($this->lmcUserAuthentication()->hasIdentity()) {
      return $this->redirect()->toUrl(
        $this->frontendUrl()->fromRoute(
          $this->game->getClassType() . '/' . $this->game->nextStep('index'),
          array('id' => $this->game->getIdentifier())
        )
      );
    }
    $request       = $this->getRequest();
    $service       = $this->getServiceLocator()->get('lmcuser_user_service');
    $form          = $this->getServiceLocator()->get('playgroundgame_register_form');
    $socialnetwork = $this->params()->fromRoute('socialnetwork', false);
    $form->setAttribute(
      'action',
      $this->frontendUrl()->fromRoute(
        $this->game->getClassType() . '/user-register',
        array('id' => $this->game->getIdentifier())
      )
    );
    $params            = array();
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
          if (!$redirect && $userOptions->getLoginRedirectRoute() != '') {
            $redirect = $this->frontendUrl()->fromRoute(
              $this->game->getClassType() . '/login',
              array('id' => $this->game->getIdentifier())
            );
          }
          $redir = $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/login',
            array('id' => $this->game->getIdentifier())
          ) . '/' . $socialnetwork . ($redirect ? '?redirect=' . $redirect : '');

          return $this->redirect()->toUrl($redir);
        }

        // Je retire la saisie du login/mdp
        $form->setAttribute(
          'action',
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/user-register',
            array(
              'id'            => $this->game->getIdentifier(),
              'socialnetwork' => $socialnetwork,

            )
          )
        );
        $form->remove('password');
        $form->remove('passwordVerify');

        $birthMonth = $infoMe->birthMonth;
        if (strlen($birthMonth) <= 1) {
          $birthMonth = '0' . $birthMonth;
        }
        $birthDay = $infoMe->birthDay;
        if (strlen($birthDay) <= 1) {
          $birthDay = '0' . $birthDay;
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
          'dob'        => $birthDay . '/' . $birthMonth . '/' . $infoMe->birthYear,
          'firstname'  => $infoMe->firstName,
          'lastname'   => $infoMe->lastName,
          'email'      => $infoMe->email,
          'postalCode' => $infoMe->zip,
        );
        $socialCredentials = array(
          'socialNetwork' => strtolower($socialnetwork),
          'socialId'      => $infoMe->identifier,
        );
      }
    }

    $redirectUrl = $this->frontendUrl()->fromRoute(
      $this->game->getClassType() . '/user-register',
      array('id' => $this->game->getIdentifier())
    ) . ($socialnetwork ? '/' . $socialnetwork : '') . ($redirect ? '?redirect=' . $redirect : '');
    $prg = $this->prg($redirectUrl, true);

    if ($prg instanceof Response) {
      return $prg;
    } elseif ($prg === false) {
      $form->setData($params);
      $viewModel = $this->buildView($this->game);
      $viewModel->setVariables(array(
        'registerForm'       => $form,
        'enableRegistration' => $userOptions->getEnableRegistration(),
        'redirect'           => $redirect,
      ));
      return $viewModel;
    }

    $post = $prg;
    if (isset($post['optin'])) {
      $post['optin'] = 1;
    }
    if (isset($post['optinPartner'])) {
      $post['optinPartner'] = 1;
    }
    $post = array_merge(
      $post,
      $socialCredentials
    );

    if ($pguserOptions->getUseRecaptcha()) {
      if (!isset($post['g-recaptcha-response']) || $post['g-recaptcha-response'] == '' || !$this->recaptcha()->recaptcha($post['g-recaptcha-response'])) {
        $this->flashMessenger()->addErrorMessage('Invalid Captcha. Please try again.');
        $form->setData($post);
        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
          'registerForm'       => $form,
          'enableRegistration' => $userOptions->getEnableRegistration(),
          'redirect'           => $redirect,
          'bah'                => 'coco',
          'flashMessages'      => $this->flashMessenger()->getMessages(),
          'flashErrors'        => $this->flashMessenger()->getErrorMessages(),
        ));

        return $viewModel;
      }
    }

    if ($this->game->getOnInvitation()) {
      $credential = trim(
        $post[$this->getGameService()->getOptions()->getOnInvitationField()]
      );
      if (!$credential) {
        $credential = $this->params()->fromQuery(
          $this->getGameService()->getOptions()->getOnInvitationField()
        );
      }
      $found = $this->getGameService()->getInvitationMapper()->findOneBy(array('requestKey' => $credential));

      if (!$found || !empty($found->getUser())) {
        $this->flashMessenger()->addMessage(
          'Authentication failed. Please try again.'
        );
        $form->setData($post);
        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
          'registerForm'       => $form,
          'enableRegistration' => $userOptions->getEnableRegistration(),
          'redirect'           => $redirect,
          'flashMessages'      => $this->flashMessenger()->getMessages(),
          'flashErrors'        => $this->flashMessenger()->getErrorMessages(),
        ));

        return $viewModel;
      }
    }

    $user = $service->register($post, 'playgroundgame_register_form');

    if (!$user) {
      $viewModel = $this->buildView($this->game);
      $viewModel->setVariables(array(
        'registerForm'       => $form,
        'enableRegistration' => $userOptions->getEnableRegistration(),
        'redirect'           => $redirect,
        'flashMessages'      => $this->flashMessenger()->getMessages(),
        'flashErrors'        => $this->flashMessenger()->getErrorMessages(),
      ));

      return $viewModel;
    }

    if ($this->game->getOnInvitation()) {
      // user has been created, associate the code with the userId
      $found->setUser($user);
      $this->getGameService()->getInvitationMapper()->update($found);
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
      $post['credential'] = isset($post['password']) ? $post['password'] : '';
      $request->setPost(new Parameters($post));

      // clear adapters
      $this->lmcUserAuthentication()->getAuthAdapter()->resetAdapters();
      $this->lmcUserAuthentication()->getAuthService()->clearIdentity();

      $logged = $this->forward()->dispatch('playgrounduser_user', array('action' => 'ajaxauthenticate'));

      if ($logged) {
        return $this->redirect()->toUrl(
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/' . $this->game->nextStep('index'),
            array('id' => $this->game->getIdentifier())
          )
        );
      } else {
        $this->flashMessenger()->setNamespace('lmcuser-login-form')->addMessage(
          'Authentication failed. Please try again.'
        );
        return $this->redirect()->toUrl(
          $this->frontendUrl()->fromRoute(
            $this->game->getClassType() . '/login',
            array('id' => $this->game->getIdentifier())
          )
        );
      }
    }

    $redirect = $this->frontendUrl()->fromRoute(
      $this->game->getClassType() . '/login',
      array('id' => $this->game->getIdentifier())
    ) . ($socialnetwork ? '/' . $socialnetwork : '') . ($redirect ? '?redirect=' . $redirect : '');

    return $this->redirect()->toUrl($redirect);
  }

  public function checkTokenAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgrounduser_user',
      array(
        'controller' => 'playgrounduser_user',
        'action' => 'check-token',
        'id' => $this->game->getIdentifier(),
        'token' => $this->params()->fromRoute('token', null)
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game' => $this->game));
    }

    return $viewModel;
  }

  public function userProfileAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgrounduser_user',
      array(
        'controller' => 'playgrounduser_user',
        'action'     => 'profile',
        'id'         => $this->game->getIdentifier()
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game'      => $this->game));
    }

    return $viewModel;
  }

  public function userresetAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgrounduser_forgot',
      array(
        'controller' => 'playgrounduser_forgot',
        'action'     => 'reset',
        'id'         => $this->game->getIdentifier(),
        'userId'     => $this->params()->fromRoute('userId', null),
        'token'      => $this->params()->fromRoute('token', null),
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game'      => $this->game));
    }

    return $viewModel;
  }

  public function ajaxforgotAction()
  {
    $view = $this->forward()->dispatch(
      'playgrounduser_forgot',
      array(
        'controller' => 'playgrounduser_forgot',
        'action'     => 'ajaxforgot',
        'id'         => $this->game->getIdentifier()
      )
    );

    if ($view && $view instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $view->setVariables(array('game'           => $this->game));
    }

    return $view;
  }

  public function cmsPageAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgroundcms',
      array(
        'controller' => 'playgroundcms',
        'action'     => 'index',
        'id'         => $this->game->getIdentifier(),
        'pid'        => $this->getEvent()->getRouteMatch()->getParam('pid')
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game'      => $this->game));
    }

    return $viewModel;
  }

  public function cmsListAction()
  {
    $viewModel = $this->forward()->dispatch(
      'playgroundcms',
      array(
        'controller' => 'playgroundcms',
        'action'     => 'list',
        'id'         => $this->game->getIdentifier(),
        'category'   => $this->game->getIdentifier()
      )
    );

    if ($viewModel && $viewModel instanceof \Laminas\View\Model\ViewModel) {
      $this->layout()->setVariables(array('game' => $this->game));
      $viewModel->setVariables(array('game'      => $this->game));
    }

    return $viewModel;
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
        if ($view && $view instanceof \Laminas\View\Model\ViewModel) {
          $viewModel->setVariables($view->getVariables());
        }
      }
    } else {
      $viewModel = new ViewModel();

      if ($game) {
        $this->addMetaTitle($game);
        //$this->addMetaBitly();
        $this->addGaEvent($game);

        $this->customizeGameDesign($game);

        $view = $this->addAdditionalView($game);
        if ($view && $view instanceof \Laminas\View\Model\ViewModel) {
          $viewModel->addChild($view, 'additional');
        } elseif ($view && $view instanceof \Laminas\Http\PhpEnvironment\Response) {
          return $view;
        }

        $this->layout()->setVariables(
          array(
            'action'        => $this->params('action'),
            'game'          => $game,
            'flashMessages' => $this->flashMessenger()->getMessages(),
          )
        );

        $viewModel->setVariables($this->getShareData($game));
        $viewModel->setVariables(
          array(
            'game' => $game,
            'user' => $this->user
          )
        );
      }
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
      $actionData   = $stepsViews[$actionName];
      if (is_string($actionData)) {
        $action     = $actionData;
        $classGame = __NAMESPACE__ . '\\' . ucfirst($this->game->getClassType());
        $controller = $this->getEvent()->getRouteMatch()->getParam('controller', $classGame);
        $view       = $this->forward()->dispatch(
          $controller,
          array(
            'action' => $action,
            'id'     => $game->getIdentifier()
          )
        );
      } elseif (is_array($actionData) && count($actionData) > 0) {
        $action     = key($actionData);
        $controller = __NAMESPACE__ . '\\' . ucfirst($actionData[$action]);
        $view       = $this->forward()->dispatch(
          $controller,
          array(
            'action' => $action,
            'id'     => $game->getIdentifier()
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
    $bitlyuser   = $this->getOptions()->getBitlyUsername();
    $bitlykey    = $this->getOptions()->getBitlyApiKey();

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
    $ga    = $this->getServiceLocator()->get('google-analytics');
    $event = new \PlaygroundCore\Analytics\Event($game->getClassType(), $this->params('action'));
    $event->setLabel(str_replace("'", "\'", $game->getTitle()));
    $ga->addEvent($event);
  }

  /**
   * @param \PlaygroundGame\Entity\Game $game
   */
  public function addMetaTitle($game)
  {
    $title = $game->getTitle();
    $this->getGameService()->getServiceManager()->get('ViewHelperManager')->get('HeadTitle')->set($title);
    // Meta set in the layout
    $this->layout()->setVariables(
      array(
        'breadcrumbTitle' => $title,
        'currentPage'     => array(
          'pageGames'      => 'games',
          'pageWinners'    => '',
        ),
        'headParams'       => array(
          'headTitle'       => $title,
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
        $this->getRequest()->getBaseUrl() . '/' . $game->getStylesheet()
      );
    }
  }

  /**
   * @param \PlaygroundGame\Entity\Game $game
   */
  public function getShareData($game)
  {
    $fo      = $this->getServiceLocator()->get('facebook-opengraph');
    $session = new Container('facebook');

    // I change the fbappid if i'm in fb
    if ($session->offsetExists('signed_request')) {
      $fo->setId($game->getFbAppId());
    }

    $fbShareDescription = $game->getFbShareDescription();

    if ($game->getFbShareImage()) {
      $fbShareImage = $this->frontendUrl()->fromRoute(
        '',
        array(),
        array('force_canonical' => true),
        false
      ) . $game->getFbShareImage();
    } else {
      $fbShareImage = $this->frontendUrl()->fromRoute(
        '',
        array(),
        array('force_canonical' => true),
        false
      ) . $game->getMainImage();
    }

    $secretKey = strtoupper(substr(sha1(uniqid('pg_', true) . '####' . time()), 0, 15));
    $socialLinkUrl = $this->frontendUrl()->fromRoute(
      $game->getClassType(),
      array('id' => $this->game->getIdentifier()),
      array('force_canonical' => true)
    ) . '?key=' . $secretKey;
    // With core shortener helper
    $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

    $fbShareUrl = $this->frontendUrl()->fromRoute(
      $game->getClassType() . '/fbshare',
      array('id' => $this->game->getIdentifier()),
      array('force_canonical' => true)
    ) . '?fbId=' . $secretKey;

    $twShareUrl = $this->frontendUrl()->fromRoute(
      $game->getClassType() . '/tweet',
      array('id' => $this->game->getIdentifier()),
      array('force_canonical' => true)
    ) . '?fbId=' . $secretKey;

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

    $twShareMessage = $game->getTwShareMessage() . $socialLinkUrl;

    // I add OG tags to the meta
    $ogTitle = new \PlaygroundCore\Opengraph\Tag('og:title', $game->getTitle());
    $ogDescription = new \PlaygroundCore\Opengraph\Tag('og:description', $fbShareDescription);
    $ogImage = new \PlaygroundCore\Opengraph\Tag('og:image', $fbShareImage);
    $twTitle = new \PlaygroundCore\Opengraph\Tag('twitter:title', $game->getTitle());
    $twDescription =  new \PlaygroundCore\Opengraph\Tag('twitter:description', $this->game->getTwShareMessage());
    $twImage =  new \PlaygroundCore\Opengraph\Tag('twitter:image', $fbShareImage);
    $twUrl = new \PlaygroundCore\Opengraph\Tag('twitter:url', $socialLinkUrl);

    $fo->addTag($ogTitle);
    $fo->addTag($ogDescription);
    $fo->addTag($ogImage);
    $fo->addTag($twTitle);
    $fo->addTag($twDescription);
    $fo->addTag($twImage);
    $fo->addTag($twUrl);

    // I add variables + js to make the share easy
    $renderer = $this->serviceLocator->get('Laminas\View\Renderer\RendererInterface');
    $headScript = $this->getServiceLocator()->get('ViewHelperManager')->get('HeadScript');
    $headScript->appendScript("var pgGameUrl = '" . $socialLinkUrl . "';\nvar pgFbShareUrl = '" . $fbShareUrl . "';\nvar pgTwShareUrl = '" . $twShareUrl . "';");
    $headScript->appendFile($renderer->frontendAssetPath() . '/js/pg/share.js');

    $data = array(
      'socialLinkUrl'         => $socialLinkUrl,
      'secretKey'             => $secretKey,
      'fbShareDescription'    => $fbShareDescription,
      'fbShareImage'          => $fbShareImage,
      'fbRequestMessage'      => $fbRequestMessage,
      'twShareMessage'        => $twShareMessage,
    );

    return $data;
  }

  /**
   * return ajax response in json format
   *
   * @param array $data
   * @return \Laminas\View\Model\JsonModel
   */
  protected function successJson($data = null)
  {
    $model = new JsonModel(array(
      'success' => true,
      'data'    => $data,
    ));
    return $model->setTerminal(true);
  }

  /**
   * return ajax response in json format
   *
   * @param string $message
   * @return \Laminas\View\Model\JsonModel
   */
  protected function errorJson($message = null)
  {
    $model = new JsonModel(array(
      'success' => false,
      'message' => $message,
    ));
    return $model->setTerminal(true);
  }

  /**
   * @param string $helperName
   */
  protected function getViewHelper($helperName)
  {
    return $this->getServiceLocator()->get('ViewHelperManager')->get($helperName);
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
