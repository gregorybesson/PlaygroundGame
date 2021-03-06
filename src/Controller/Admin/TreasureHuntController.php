<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Game;

use PlaygroundGame\Entity\TreasureHunt;
use PlaygroundGame\Entity\TreasureHuntPuzzle;

use PlaygroundGame\Controller\Admin\GameController;
use Laminas\View\Model\ViewModel;

class TreasureHuntController extends GameController
{
    /**
     * @var GameService
     */
    protected $adminGameService;

    protected $treasurehunt;

    public function createTreasureHuntAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/treasure-hunt/treasurehunt');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $treasurehunt = new TreasureHunt();

        $form = $this->getServiceLocator()->get('playgroundgame_treasurehunt_form');
        $form->bind($treasurehunt);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/create-treasurehunt', array('gameId' => 0)));
        $form->setAttribute('method', 'post');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if(empty($data['prizes'])){
                $data['prizes'] = array();
            }
            $game = $service->createOrUpdate($data, $treasurehunt, 'playgroundgame_treasurehunt_form');
            if ($game) {

                $this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The game has been created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $treasurehunt));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create treasurehunt', 'treasurehunt' => $treasurehunt));
    }

    public function editTreasureHuntAction()
    {
        $this->checkGame();

        return $this->editGame(
            'playground-game/treasure-hunt/treasurehunt',
            'playgroundgame_treasurehunt_form'
        );
    }

		public function corsAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
				$viewModel->setTemplate('playground-game/treasure-hunt/cors');
        return $viewModel;
    }

    public function areapickerAction()
    {
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->setTemplate('playground-game/treasure-hunt/areapicker');
    	return $viewModel;
    }

    public function puzzleDeleteImageAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
        $imageId = $this->getEvent()->getRouteMatch()->getParam('imageId');

        if (!$puzzleId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $puzzle   = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);

        $images = json_decode($puzzle->getImage(), true);

        unset($images[$imageId]);

        $puzzle->setImage(json_encode($images));
        $service->getTreasureHuntPuzzleMapper()->update($puzzle);

        return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-edit', array('gameId'=>$gameId, 'puzzleId'=>$puzzleId));
    }

    public function listPuzzleAction()
    {
    	$service 	= $this->getAdminGameService();
    	$gameId 	= $this->getEvent()->getRouteMatch()->getParam('gameId');
    	$filter		= $this->getEvent()->getRouteMatch()->getParam('filter');

    	if (!$gameId) {
    		return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}

    	$puzzles = $service->getTreasureHuntPuzzleMapper()->findByGameId($gameId);
    	$game = $service->getGameMapper()->findById($gameId);

    	if (is_array($puzzles)) {
    		$paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($puzzles));
    		$paginator->setItemCountPerPage(50);
    		$paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
    	} else {
    		$paginator = $puzzles;
    	}

    	return new ViewModel(
            array(
                'puzzles'     => $paginator,
                'gameId' 	  => $gameId,
                'filter'	  => $filter,
                'game' 		  => $game,
            )
    	);
    }

    public function addPuzzleAction()
    {
    	$viewModel = new ViewModel();
    	$viewModel->setTemplate('playground-game/treasure-hunt/puzzle');
    	$service = $this->getAdminGameService();
    	$gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
    	if (!$gameId) {
    		return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}

    	$form = $this->getServiceLocator()->get('playgroundgame_treasurehuntpuzzle_form');
    	$form->get('submit')->setAttribute('label', 'Add');
    	$form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/treasurehunt-puzzle-add', array('gameId' => $gameId)));
    	$form->setAttribute('method', 'post');
    	$form->get('treasurehunt_id')->setAttribute('value', $gameId);

    	$puzzle = new TreasureHuntPuzzle();
    	$form->bind($puzzle);

    	if ($this->getRequest()->isPost()) {
    		$data = array_merge(
    			$this->getRequest()->getPost()->toArray(),
    			$this->getRequest()->getFiles()->toArray()
    		);

    		$puzzle = $service->createPuzzle($data);
    		if ($puzzle) {
    		    $service->uploadImages($puzzle, $data);
    			// Redirect to list of games
    			$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The puzzle has been created');

    			return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-list', array('gameId'=>$gameId));
    		}
    	}

    	return $viewModel->setVariables(
    		array(
    			'form'       => $form,
   				'gameId'     => $gameId,
   				'puzzle_id'  => 0,
   				'title'      => 'Add puzzle',
    		    'puzzle'     => $puzzle
    		)
    	);
    }

    public function editPuzzleAction()
    {
    	$service = $this->getAdminGameService();
    	$viewModel = new ViewModel();
    	$viewModel->setTemplate('playground-game/treasure-hunt/puzzle');

    	$gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');

    	$puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
    	if (!$puzzleId) {
    		return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}
    	$puzzle   = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);
    	$treasurehuntId     = $puzzle->getTreasureHunt()->getId();

    	$form = $this->getServiceLocator()->get('playgroundgame_treasurehuntpuzzle_form');
    	$form->get('submit')->setAttribute('label', 'Add');
    	$form->get('treasurehunt_id')->setAttribute('value', $treasurehuntId);

    	$form->bind($puzzle);

    	if ($this->getRequest()->isPost()) {
    		$data = array_merge(
    				$this->getRequest()->getPost()->toArray(),
    				$this->getRequest()->getFiles()->toArray()
    		);
    		$puzzle = $service->updatePuzzle($data, $puzzle);
    		if ($puzzle) {
    		    $service->uploadImages($puzzle, $data);
    			// Redirect to list of games
    			$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The puzzle was updated');

    			return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-list', array('gameId'=>$treasurehuntId));
    		}
    	}

    	return $viewModel->setVariables(
    		array(
    			'form'       => $form,
    			'gameId'     => $treasurehuntId,
   				'puzzle_id'  => $puzzleId,
   				'title'      => 'Edit puzzle',
    		    'puzzle'     => $puzzle
   			)
    	);
    }

    public function removePuzzleAction()
    {
    	$service = $this->getAdminGameService();
    	$puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
    	if (!$puzzleId) {
    		return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}
    	$puzzle   = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);
    	$treasurehuntId = $puzzle->getTreasureHunt()->getId();

    	$service->getTreasureHuntPuzzleMapper()->remove($puzzle);
    	$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The puzzle was deleted');

    	return $this->redirect()->toRoute('admin/treasurehuntadmin/treasurehunt-puzzle-list', array('gameId'=>$treasurehuntId));
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_treasurehunt_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
