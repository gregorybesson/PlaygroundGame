<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Mission;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MissionController extends AbstractActionController
{

    /**
     * @var missionService
     */
    protected $missionService;

    /**
    * listAction : retrieve all leaderboardtype
    *
    * @return array $return 
    */
    public function listAction()
    {
        $missions = $this->getMissionService()->getMissionMapper()->findAll();
        return array("missions" => $missions);
    }

    /**
    * createAction : create a leaderboardtype
    *
    * @return viewModel $viewModel 
    */
    public function createAction()
    {

        $form = $this->getServiceLocator()->get('playgroundgame_mission_form');
        
        $request = $this->getRequest();
        $mission = new Mission();
        
        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $mission = $this->getMissionService()->create($data, 'playgroundgame_mission_form');
            if ($mission) {
                $this->flashMessenger()->addMessage('The mission "'.$mission->getTitle().'" was created');

                return $this->redirect()->toRoute('admin/mission/list');
            } else {
                 $this->flashMessenger()->addMessage('The mission was not created');

                return $this->redirect()->toRoute('admin/mission/list');
            }
            
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        return $viewModel->setVariables(array('form' => $form));
    }


    /**
    * editAction : edit a leaderboardtype
    *
    * @return viewModel $viewModel 
    */
    public function editAction()
    {

        $missionId = $this->getEvent()->getRouteMatch()->getParam('missionId');
        $mission = $this->getMissionService()->findById($missionId);

        $form = $this->getServiceLocator()->get('playgroundgame_mission_form');

        $request = $this->getRequest();

        $form->bind($mission);

        if ($request->isPost()) {
            $data = array_merge(
                    $request->getPost()->toArray(),
                    $request->getFiles()->toArray()
            );

            $mission = $this->getMissionService()->edit($data, $mission, 'playgroundgame_mission_form');

            if ($mission) {
                $this->flashMessenger()->addMessage('The mission "'.$mission->getTitle().'" was updated');

                return $this->redirect()->toRoute('admin/mission/list');
            } else {
                 $this->flashMessenger()->addMessage('The mission was not updated');

                return $this->redirect()->toRoute('admin/mission/list');
            }
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        return $viewModel->setVariables(array('form' => $form));
    }

    /**
    * deleteAction : delete a leaderboardtype
    *
    * @return redirect 
    */
    public function deleteAction()
    {
        $missionId = $this->getEvent()->getRouteMatch()->getParam('missionId');
        $mission = $this->getMissionService()->findById($missionId);
        $this->getMissionService()->remove($mission);

        return $this->redirect()->toRoute('admin/mission/list');
    }

    public function associateAction()
    {
        $missionId = $this->getEvent()->getRouteMatch()->getParam('missionId');
        $mission = $this->getMissionService()->findById($missionId);

        $form = $this->getServiceLocator()->get('playgroundgame_mission_game_form');
        $request = $this->getRequest();

        if ($request->isPost()) {
            var_dump($request->getPost()->toArray());
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/associateGame');

        return $viewModel->setVariables(array('mission' => $mission, 'form' => $form));
    }

     /**
     * Retrieve service leaderboardType instance
     *
     * @return Service/leaderboardType leaderboardTypeService
     */
    public function getMissionService()
    {
        if (null === $this->missionService) {           
            $this->missionService = $this->getServiceLocator()->get('playgroundgame_mission_service');
        }

        return $this->missionService;
    }
}
