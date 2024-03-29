<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Mission;
use PlaygroundGame\Controller\Admin\GameController;
use PlaygroundGame\Service\Game as AdminGameService;
use Laminas\View\Model\ViewModel;

class MissionController extends GameController
{
    /**
     * @var GameService
     */
    protected $adminGameService;

    protected $mission;

    public function createMissionAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $mission = new Mission();

        $form = $this->getServiceLocator()->get('playgroundgame_mission_form');
        $form->bind($mission);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-mission',
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

            $game = $service->createOrUpdate($data, $mission, 'playgroundgame_mission_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('mission')->addMessage('The game has been created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $mission));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create mission', 'mission' => $mission));
    }

    public function editMissionAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');

        if (!$gameId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/createMission'));
        }

        $game = $service->getGameMapper()->findById($gameId);
        $navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-mission');
        $page->setParams(['gameId' => $game->getId()]);
        $page->setLabel($game->getTitle());

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $form   = $this->getServiceLocator()->get('playgroundgame_mission_form');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/edit-mission',
                array('gameId' => $gameId)
            )
        );
        $form->setAttribute('method', 'post');
        if ($game->getFbAppId()) {
            $appIds = $form->get('fbAppId')->getOption('value_options');
            $appIds[$game->getFbAppId()] = $game->getFbAppId();
            $form->get('fbAppId')->setAttribute('options', $appIds);
        }

        $gameOptions = $this->getAdminGameService()->getOptions();
        $gameStylesheet = $gameOptions->getMediaPath() . '/' . 'stylesheet_'. $game->getId(). '.css';
        if (is_file($gameStylesheet)) {
            $values = $form->get('stylesheet')->getValueOptions();
            $values[$gameStylesheet] = 'Style personnalisé de ce jeu';

            $form->get('stylesheet')->setAttribute('options', $values);
        }

        $form->bind($game);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }

            $result = $service->createOrUpdate($data, $game, 'playgroundgame_mission_form');

            if ($result) {
                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }

        $gameForm->setVariables(array('form' => $form, 'game' => $game));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Edit mission', 'mission' => $game));
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_mission_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
