<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\Lottery;
use PlaygroundGame\Controller\Admin\GameController;
use Laminas\View\Model\ViewModel;

class LotteryController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    public function createLotteryAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/lottery/lottery');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $lottery = new Lottery();

        $form = $this->getServiceLocator()->get('playgroundgame_lottery_form');
        $form->bind($lottery);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-lottery',
                array('gameId' => 0)
            )
        );
        $form->setAttribute('method', 'post');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }
            if (isset($data['drawDate']) && $data['drawDate']) {
                $data['drawDate'] = \DateTime::createFromFormat('d/m/Y', $data['drawDate']);
            }
            $game = $service->createOrUpdate($data, $lottery, 'playgroundgame_lottery_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $lottery));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create lottery'));
    }

    public function editLotteryAction()
    {
        $this->checkGame();

        $navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-lottery');
        $page->setParams(['gameId' => $this->game->getId()]);
        $page->setLabel($this->game->getTitle());

        return $this->editGame(
            'playground-game/lottery/lottery',
            'playgroundgame_lottery_form'
        );
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_lottery_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
