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
     * @var missionGameService
     */
    protected $missionGameService;

    /**
    * listAction : retrieve all missions
    *
    * @return array $return 
    */
    public function listAction()
    {
        $missions = $this->getMissionService()->getMissionMapper()->findAll();
        $missionsHasGames = array();
        
        foreach ($missions as $mission) {
            $missionsHasGames[$mission->getId()] = count($this->getMissionGameService()->findMissionGameByMission($mission));
        }

        return array("missions" => $missions,
                     "missionsHasGames" => $missionsHasGames);
    }

    /**
    * createAction : create a mission
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
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The mission "'.$mission->getTitle().'" was created');

                return $this->redirect()->toRoute('admin/mission/list');
            } else {
                 $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The mission was not created');

                return $this->redirect()->toRoute('admin/mission/list');
            }
            
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        return $viewModel->setVariables(array('form' => $form));
    }


    /**
    * editAction : edit a mission
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

            $form->setData($data);
            
            if ($form->isValid()) {

                $mission = $this->getMissionService()->edit($mission, $data);
            }else{
                $mission = null;
            }

            if ($mission) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The mission "'.$mission->getTitle().'" was updated');
            } else {
                 $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The mission was not updated');
            }
            
            return $this->redirect()->toRoute('admin/mission/list');
        }

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/mission/mission');

        return $viewModel->setVariables(array('form' => $form));
    }

    /**
    * deleteAction : delete a mission
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

    public function desactivateAction()
    {
        $missionId = $this->getEvent()->getRouteMatch()->getParam('missionId');
        $mission = $this->getMissionService()->findById($missionId);
        $mission->setActive(false);
        $this->getMissionService()->update($mission);

        return $this->redirect()->toRoute('admin/mission/list');
    }

    public function activateAction()
    {
        $missionId = $this->getEvent()->getRouteMatch()->getParam('missionId');
        $mission = $this->getMissionService()->findById($missionId);
        $mission->setActive(true);
        $this->getMissionService()->update($mission);

        return $this->redirect()->toRoute('admin/mission/list');
    }

     /**
     * Retrieve service mission instance
     *
     * @return Service/Mission missionService
     */
    public function getMissionService()
    {
        if (null === $this->missionService) {           
            $this->missionService = $this->getServiceLocator()->get('playgroundgame_mission_service');
        }

        return $this->missionService;
    }

    /**
     * Retrieve service MissionGame instance
     *
     * @return Service/MissionGame missionGameService
     */
    public function getMissionGameService()
    {
        if (null === $this->missionGameService) {           
            $this->missionGameService = $this->getServiceLocator()->get('playgroundgame_mission_game_service');
        }

        return $this->missionGameService;
    }
}
