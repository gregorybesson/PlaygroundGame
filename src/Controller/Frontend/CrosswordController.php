<?php
namespace PlaygroundGame\Controller\Frontend;

use Laminas\ServiceManager\ServiceLocatorInterface;

class CrosswordController extends GameController
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
                // the user has not enough points to buy an entry to this game
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
            $this->getGameService()->crosswordScore($this->game, $this->user, $data);

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'crossword/result',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $rows = $this->game->getLayoutRows();
        $columns = $this->game->getLayoutColumns();
        $acrossClues = [];
        $downClues = [];
        $words = $this->getGameService()->getCrosswordWordMapper()->queryByGame($this->game)->getResult();

        foreach ($words as $word) {
            if ($word->getOrientation() == "across"){
                $acrossClues[] = [
                    "x" => $word->getLayoutColumn(),
                    "y" => $word->getLayoutRow(),
                    "clue" => $word->getPosition() . '. ' . trim($word->getClue()) . ' (' . strlen($word->getSolution()) . ')',
                ];
            } else if ($word->getOrientation() == "down"){
                $downClues[] = [
                    "x" => $word->getLayoutColumn(),
                    "y" => $word->getLayoutRow(),
                    "clue" => $word->getPosition() . '. ' . trim($word->getClue()) . ' (' .strlen($word->getSolution()) . ')',
                ];
            }
        }
        $crosswordDefinition = [
            "width" => $columns,
            "height" => $rows,
            "acrossClues" => $acrossClues,
            "downClues" => $downClues,
        ];

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
            'crosswordDefinition' => json_encode($crosswordDefinition),
            'acrossClues' => $acrossClues,
            'downClues' => $downClues,
        ));

        return $viewModel;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_crossword_service');
        }

        return $this->gameService;
    }
}