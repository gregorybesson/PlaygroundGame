<?php
namespace PlaygroundGame\Controller\Frontend;

use Zend\ServiceManager\ServiceLocatorInterface;

class LotteryController extends GameController
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
                $this->flashMessenger()->addMessage('Vous avez déjà participé');
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

        $entry = $this->getGameService()->subscribeToLottery($this->game, $this->user, $entry);

        return $this->redirect()->toUrl(
            $this->frontendUrl()->fromRoute(
                $this->game->getClassType() . '/'. $this->game->nextStep($this->params('action')),
                array('id' => $this->game->getIdentifier())
            )
        );
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
                    'lottery',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
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
        
        if (!$playLimitReached) {
            $this->getGameService()->sendMail($this->game, $this->user, $lastEntry);
        }

        $viewModel->setVariables(array(
                'statusMail'    => $statusMail,
                'form'          => $form,
                'playLimitReached' => $playLimitReached,
                'entry' => $lastEntry
            ));

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
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
         )));

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
        $response->setContent(\Zend\Json\Json::encode(array(
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
        $response->setContent(\Zend\Json\Json::encode(array(
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
