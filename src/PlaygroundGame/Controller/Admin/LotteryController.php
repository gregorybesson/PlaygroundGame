<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\Lottery;
use PlaygroundGame\Controller\Admin\GameController;
use Zend\View\Model\ViewModel;

class LotteryController extends GameController
{
    /**
     * @var GameService
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
            $this->url()->fromRoute(
                'admin/playgroundgame/create-lottery',
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
            $game = $service->create($data, $lottery, 'playgroundgame_lottery_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $lottery));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create lottery'));
    }

    public function editLotteryAction()
    {
        $this->checkGame();

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/lottery/lottery');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $form = $this->getServiceLocator()->get('playgroundgame_lottery_form');
        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'admin/playgroundgame/edit-lottery',
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');
        if ($this->game->getFbAppId()) {
            $appIds = $form->get('fbAppId')->getOption('value_options');
            $appIds[$this->game->getFbAppId()] = $this->game->getFbAppId();
            $form->get('fbAppId')->setAttribute('options', $appIds);
        }

        $gameOptions = $this->getAdminGameService()->getOptions();
        $gameStylesheet = $gameOptions->getMediaPath() . '/' . 'stylesheet_'. $this->game->getId(). '.css';
        if (is_file($gameStylesheet)) {
            $values = $form->get('stylesheet')->getValueOptions();
            $values[$gameStylesheet] = 'Style personnalisé de ce jeu';

            $form->get('stylesheet')->setAttribute('options', $values);
        }

        $form->bind($this->game);

        if ($this->getRequest()->isPost()) {
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
            $result = $service->edit($data, $this->game, 'playgroundgame_lottery_form');

            if ($result) {
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(array('form' => $form, 'game' => $this->game));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Edit lottery'));
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
