<?php
namespace PlaygroundGame\Controller\Frontend;

class TradingCardController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {
        $entry = $this->getGameService()->play($this->game, $this->user);
        $viewModel = $this->buildView($this->game);
        $booster = null;
        if ($entry) {
            $booster = $this->getGameService()->getBooster($this->game, $this->user, $entry);
        }
        
        $album = $this->getGameService()->getAlbum($this->game, $this->user);
        $viewModel->setVariables(array('booster' => $booster, 'album' => $album));

        return $viewModel;
    }

    public function resultAction()
    {
        $album = $this->getGameService()->getAlbum($this->game, $this->user);
        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array('album' => $album));

        return $viewModel;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_tradingcard_service');
        }

        return $this->gameService;
    }
}
