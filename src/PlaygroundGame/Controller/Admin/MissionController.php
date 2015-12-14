<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Mission;
use PlaygroundGame\Controller\Admin\GameController;
use PlaygroundGame\Service\Game as AdminGameService;
use Zend\View\Model\ViewModel;

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
        $viewModel->setTemplate('playground-game/game/mission');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $mission = new Mission();

        $form = $this->getServiceLocator()->get('playgroundgame_mission_form');
        $form->bind($mission);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'admin/playgroundgame/create-mission',
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

                $this->flashMessenger()->setNamespace('mission')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
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
            return $this->redirect()->toRoute('admin/playgroundgame/createMission');
        }

        $game = $service->getGameMapper()->findById($gameId);

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $form   = $this->getServiceLocator()->get('playgroundgame_mission_form');
        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'admin/playgroundgame/edit-mission',
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

                /**
                 *   The following work has to be done there because doctrine hydration with nested objects is a mess
                 *   I've been obliged to proceed this way... haven't found the bugfix.
                 */
                /*$cmg = count($result->getMissionGames());
                if( $cmg < count($data['missionGames']) ){
                    $i=0;
                    foreach($data['missionGames'] as $m){
                        if( $i >= $cmg ){
                            $g = $service->getGameMapper()->findById($m['game']);
                            $mg = new \PlaygroundGame\Entity\MissionGame();
                            $mg->setGame($g);
                            $mg->setPosition($m['position']);
                            $mg->setMission($result);
                            $result->addMissionGame($mg);
                        }
                        $i++;
                    }
                    $service->getGameMapper()->update($result);
                }*/
                return $this->redirect()->toRoute('admin/playgroundgame/list');
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
