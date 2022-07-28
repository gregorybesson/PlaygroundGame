<?php
namespace PlaygroundGame\Controller\Frontend;

use Laminas\ServiceManager\ServiceLocatorInterface;

class MemoryController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    public function playAction()
    {
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

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $attempts = filter_var($data['attempts'], FILTER_SANITIZE_NUMBER_INT);
            $mistakes = filter_var($data['mistakes'], FILTER_SANITIZE_NUMBER_INT);
            $duration = filter_var($data['duration'], FILTER_SANITIZE_NUMBER_INT);
            $dataSanitized = ['attempts' => $attempts, 'mistakes' => $mistakes, 'duration' => $duration];
            $this->getGameService()->memoryScore($this->game, $this->user, $dataSanitized);
            
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'memory/result',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }
        
        $viewModel = $this->buildView($this->game);

        return $viewModel;
    }

    public function resultAction()
    {
        $statusMail = null;
        $playLimitReached = false;
        if ($this->getRequest()->getQuery()->get('playLimitReached')) {
            $playLimitReached = true;
        }

        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'memory',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
                )
            );
        }

        // Je recherche le score associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $score = $this->getGameService()->getMemoryScoreMapper()->findOneBy(array('entry' => $lastEntry));

        if (! $score) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'memory',
                    array('id' => $this->game->getIdentifier())
                )
            );
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
                    $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
                }
            }
        }

        // buildView must be before sendMail because it adds the game template path to the templateStack
        $viewModel = $this->buildView($this->game);

        $viewModel->setVariables(
            array(
                'statusMail'    => $statusMail,
                'form'          => $form,
                'playLimitReached' => $playLimitReached,
                'entry' => $lastEntry,
                'score' => $score,
            )
        );

        return $viewModel;
    }

    public function fbshareAction()
    {
        $result = parent::fbshareAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(
            \Laminas\Json\Json::encode(
                array(
                    'success' => $result,
                    'playBonus' => $bonusEntry
                )
            )
        );

        return $response;
    }

    public function fbrequestAction()
    {
        $result = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
        $result = parent::tweetAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
        $result = parent::googleAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_memory_service');
        }

        return $this->gameService;
    }
}
