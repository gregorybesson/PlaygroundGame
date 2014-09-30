<?php
namespace PlaygroundGame\Controller\Frontend;

use Zend\View\Model\ViewModel;

class MissionController extends GameController
{

    protected $missionService;

    public function indexAction()
    {
        $layoutViewModel = $this->layout();
                
        $missions = $this->getMissionService()->getActiveMissions();
        
        return new ViewModel(array(
            'missions' => $missions
        ));
    }

    public function getMissionService()
    {
        if (! $this->missionService) {
            $this->missionService = $this->getServiceLocator()->get('playgroundgame_mission_service');
        }
        
        return $this->missionService;
    }
}
