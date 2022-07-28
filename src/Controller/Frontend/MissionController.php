<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Controller\Frontend\GameController;
use PlaygroundGame\Service\GameService;
use Laminas\Session\Container;
use Laminas\ServiceManager\ServiceLocatorInterface;

class MissionController extends GameController
{

    /**
     * @var gameService
     */
    protected $gameService;
    protected $mission;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    /**
     * Homepage of the game
     */
    public function indexAction()
    {
        $entry = $this->getGameService()->checkExistingEntry($this->game, $this->user);
        $games = $this->getGameService()->getMissionGames($this->game, $this->user);

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(
            array(
            'user'          => $this->user,
            'games'         => $games,
            'entry'         => $entry,
            'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );

        return $viewModel;
    }

    public function playAction()
    {
        $subGameIdentifier = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $playError = null;
        $entry = $this->getGameService()->play($this->game, $this->user, $playError);
        if (!$entry) {
            $reason = "";
            if ($playError === -1) {
                // the user has already taken part to this game and the participation limit has been reached
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate("You have already played", "playgroundgame"));
                $reason = '?playLimitReached=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType().'/result',
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            } elseif ($playError === -2) {
                // the user has not accepted the mandatory rules of the game
                $this->flashMessenger()->addMessage('Vous devez accepter le réglement');
                $reason = '?NoOptin=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType(),
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            } elseif ($playError === -3) {
                // the user has enough points to buy an entry to this game
                $this->flashMessenger()->addMessage("Vous ne pouvez pas acheter la partie");
                $reason = '?NotPaid=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType(),
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            }

            return $this->redirect()->toUrl($noEntryRedirect);
        }

        if (!$subGameIdentifier) {
            $subGame = $this->game->getNextPlayableGame($entry);
        } else {
            $subGame = $this->getGameService()->checkGame($subGameIdentifier);
        }

        if (!$this->game->isPlayable($subGame, $entry)) {
          // this subgame is not playable
            $this->flashMessenger()->addMessage('No game found');

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'mission',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $session = new Container('facebook');

        if (!$this->user) {
          // The game is deployed on Facebook, and played from Facebook : retrieve/register user
            if ($session->offsetExists('signed_request')) {
              // Get Playground user from Facebook info
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch(
                    'playgrounduser_user',
                    array(
                    'controller' => 'playgrounduser_user',
                    'action'     => 'registerFacebookUser',
                    'provider'   => 'facebook',
                    )
                );

                $this->layout()->setTemplate($beforeLayout);
                $this->user = $view->user;

              // If the user cannot be created/retrieved from Facebook info, redirect to login/register form
                if (!$this->user) {
                    $redirect = urlencode(
                        $this->frontendUrl()->fromRoute(
                            'mission/play',
                            array('id'              => $this->game->getIdentifier()),
                            array('force_canonical' => true)
                        )
                    );
                    if (array_search('login', $this->game->getStepsArray())) {
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute('mission/login').'?redirect='.$redirect
                        );
                    } else {
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute('lmcuser/register').'?redirect='.$redirect
                        );
                    }
                }

              // The game is not played from Facebook : redirect to login/register form
            } elseif (!$this->game->getAnonymousAllowed()) {
                $redirect = urlencode(
                    $this->frontendUrl()->fromRoute(
                        'mission/play',
                        array('id'              => $this->game->getIdentifier()),
                        array('force_canonical' => true)
                    )
                );

                if (array_search('login', $this->game->getStepsArray())) {
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute('mission/login').'?redirect='.$redirect
                    );
                } else {
                    return $this->redirect()->toUrl(
                        $this->frontendUrl()->fromRoute('lmcuser/register').'?redirect='.$redirect
                    );
                }
            }
        }

        $beforeLayout = $this->layout()->getTemplate();
        $classGame = __NAMESPACE__ . '\\' . ucfirst($subGame->getClassType());

        $subViewModel = $this->forward()->dispatch(
            $classGame,
            array(
                'controller' => $classGame,
                'action'     => 'home',
                'id'         => $subGame->getIdentifier()
            )
        );

        // If the subgame redirect to the result page
        if ($this->getResponse()->getStatusCode() == 302) {

            $entry = $this->getGameService()->missionWinner($this->game, $this->user, $entry, $subGame);
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'mission/result',
                    array(
                        'id'     => $this->game->getIdentifier(),
                        'gameId' => $subGame->getIdentifier(),
                        'missionEntry' => $entry,
                    ),
                    array('force_canonical' => true)
                )
            );
        }

      // suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);

      // give the ability to the mission to have its customized look and feel.
        $templatePathResolver = $this->getServiceLocator()->get('Laminas\View\Resolver\TemplatePathStack');
        $l                    = $templatePathResolver->getPaths();
      // I've already added the path for the game so the base path is $l[1]
        $templatePathResolver->addPath($l[1].'custom/'.$this->game->getIdentifier());

        $subViewModel->mission = $this->game;

        return $subViewModel;
    }

    public function resultAction()
    {
        $subGameIdentifier = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $subGame           = $this->getGameService()->checkGame($subGameIdentifier);

        if (!$this->user && !$this->game->getAnonymousAllowed()) {
            $redirect = urlencode(
                $this->frontendUrl()->fromRoute(
                    'mission/result',
                    array('id' => $this->game->getIdentifier())
                )
            );

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute('lmcuser/register').'?redirect='.$redirect
            );
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        $beforeLayout = $this->layout()->getTemplate();
        $classGame = __NAMESPACE__ . '\\' . ucfirst($subGame->getClassType());
        $subViewModel = $this->forward()->dispatch(
            $classGame,
            array(
                'controller' => $classGame,
                'action'     => 'result',
                'id'         => $subGameIdentifier
            )
        );

        if ($this->getResponse()->getStatusCode() == 302) {
            $this->getResponse()->setStatusCode('200');

            $classGame = __NAMESPACE__ . '\\' . ucfirst($subGame->getClassType());
            $subViewModel = $this->forward()->dispatch(
                $classGame,
                array(
                    'controller' => $classGame,
                    'action'     => 'result',
                    'id'         => $subGameIdentifier
                )
            );
        }
        // suite au forward, le template de layout a changé, je dois le rétablir...
        $this->layout()->setTemplate($beforeLayout);

        // give the ability to the mission to have its customized look and feel.
        $templatePathResolver = $this->getServiceLocator()->get('Laminas\View\Resolver\TemplatePathStack');
        $l                    = $templatePathResolver->getPaths();
        // I've already added the path for the game so the base path is $l[1]
        $templatePathResolver->addPath($l[1].'custom/'.$this->game->getIdentifier());

        $subViewModel->mission = $this->game;
        $subViewModel->subGames = $this->getGameService()->getMissionGames($this->game, $this->user);
        return $subViewModel;
    }

    public function fbshareAction()
    {
        $sg         = $this->getGameService();
        $result     = parent::fbshareAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user       = $this->lmcUserAuthentication()->getIdentity();
            $game       = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
                    'success'   => $result,
                    'playBonus' => $bonusEntry,
                )));

        return $response;
    }

    public function fbrequestAction()
    {
        $sg         = $this->getGameService();
        $result     = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user       = $this->lmcUserAuthentication()->getIdentity();
            $game       = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
                    'success'   => $result,
                    'playBonus' => $bonusEntry,
                )));

        return $response;
    }

    public function tweetAction()
    {
        $sg         = $this->getGameService();
        $result     = parent::tweetAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user       = $this->lmcUserAuthentication()->getIdentity();
            $game       = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
                    'success'   => $result,
                    'playBonus' => $bonusEntry,
                )));

        return $response;
    }

    public function googleAction()
    {
        $sg         = $this->getGameService();
        $result     = parent::googleAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user       = $this->lmcUserAuthentication()->getIdentity();
            $game       = $sg->checkGame($identifier);
            $bonusEntry = $sg->addAnotherChance($game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Laminas\Json\Json::encode(array(
                    'success'   => $result,
                    'playBonus' => $bonusEntry,
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
