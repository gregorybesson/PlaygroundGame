<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Game;
use PlaygroundGame\Service\Game as AdminGameService;
use PlaygroundGame\Entity\TreasureHunt;
use PlaygroundGame\Entity\TreasureHuntPuzzle;
use PlaygroundGame\Entity\TreasureHuntPuzzlePiece;

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

				$navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-treasurehunt');
        $page->setParams(['gameId' => $this->game->getId()]);
        $page->setLabel($this->game->getTitle());

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

		public function puzzleDeleteRefImageAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
        $imageId = $this->getEvent()->getRouteMatch()->getParam('imageId');

        if (!$puzzleId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $puzzle   = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);

        $images = json_decode($puzzle->getReferenceImage(), true);

        unset($images[$imageId]);

        $puzzle->setReferenceImage(json_encode($images));
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

			$navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
			$page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-treasurehunt');
			$page->setParams(['gameId' => $game->getId()]);
			$page->setLabel($game->getTitle());

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

			$game = $service->getGameMapper()->findById($gameId);
			$navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
			$page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-treasurehunt');
			$page->setParams(['gameId' => $game->getId()]);
			$page->setLabel($game->getTitle());

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

			$navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
			$page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-treasurehunt');
			$page->setParams(['gameId' => $puzzle->getTreasureHunt()->getId()]);
			$page->setLabel($puzzle->getTreasureHunt()->getTitle());

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
    			$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The puzzle has been updated');

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
    	$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The puzzle has been deleted');

    	return $this->redirect()->toRoute('admin/treasurehuntadmin/treasurehunt-puzzle-list', array('gameId'=>$treasurehuntId));
    }

		public function listPiecesAction()
		{
			$service = $this->getAdminGameService();
			$puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
			$pieces = $service->getTreasureHuntPuzzlePieceMapper()->findBy(array('puzzle' => $puzzleId));
			$treasurehuntPuzzle = $service->getTreasureHuntMapper()->findById($puzzleId);
			if (is_array($pieces)) {
    		$paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($pieces));
    		$paginator->setItemCountPerPage(50);
    		$paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
    	} else {
    		$paginator = $pieces;
    	}
			$viewModel = new ViewModel();
			$viewModel->setTemplate('playground-game/treasure-hunt/list-piece');
			$viewModel->setVariables(
				array(
					'pieces' => $paginator,
					'treasurehuntPuzzle' => $treasurehuntPuzzle,
					'puzzleId' => $puzzleId,
				)
			);

			return $viewModel;
		}

		public function addPieceAction()
		{
			$service = $this->getAdminGameService();
			$viewModel = new ViewModel();
			$viewModel->setTemplate('playground-game/treasure-hunt/piece');
			$puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
			if (!$puzzleId) {
				return $this->redirect()->toRoute('admin/playgroundgame/list');
			}
			$treasurehuntPuzzle = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);

			$form = $this->getServiceLocator()->get('playgroundgame_treasurehuntpuzzle_piece_form');
			$form->get('submit')->setAttribute('label', 'Add');
			$form->get('puzzle_id')->setAttribute('value', $puzzleId);

			$piece = new TreasureHuntPuzzlePiece();
    	$form->bind($piece);

			if ($this->getRequest()->isPost()) {
				$data = array_merge(
						$this->getRequest()->getPost()->toArray(),
						$this->getRequest()->getFiles()->toArray()
				);
				$piece = $service->createPiece($data);
				if ($piece) {
					// Redirect to list of games
					$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The piece has been updated');

					return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-piece-list', array('puzzleId'=>$puzzleId));
				}
			}

			return $viewModel->setVariables(
				array(
					'form'       => $form,
					'piece_id'   => 0,
					'title'      => 'Add a piece',
					'piece'      => $piece,
					'puzzle'		 => $treasurehuntPuzzle,
				)
			);
		}

		public function editPieceAction()
		{
			$service = $this->getAdminGameService();
			$viewModel = new ViewModel();
			$viewModel->setTemplate('playground-game/treasure-hunt/piece');
			$pieceId = $this->getEvent()->getRouteMatch()->getParam('pieceId');
			$puzzleId = $this->getEvent()->getRouteMatch()->getParam('puzzleId');
			if (!$pieceId) {
				return $this->redirect()->toRoute('admin/playgroundgame/list');
			}
			$piece   = $service->getTreasureHuntPuzzlePieceMapper()->findById($pieceId);
			$treasurehuntPuzzle = $service->getTreasureHuntPuzzleMapper()->findById($puzzleId);

			$form = $this->getServiceLocator()->get('playgroundgame_treasurehuntpuzzle_piece_form');
			$form->get('submit')->setAttribute('label', 'Add');
			$form->get('puzzle_id')->setAttribute('value', $puzzleId);
			$form->bind($piece);
			if ($this->getRequest()->isPost()) {
				$data = array_merge(
						$this->getRequest()->getPost()->toArray(),
						$this->getRequest()->getFiles()->toArray()
				);
				$piece = $service->updatePiece($data, $piece);
				if ($piece) {
					// Redirect to list of games
					$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The piece has been updated');
					return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-piece-list', array('puzzleId'=>$puzzleId));
				}
			}

			return $viewModel->setVariables(
				array(
					'form'       => $form,
					'puzzleId'   => $puzzleId,
					'piece_id'   => $pieceId,
					'title'      => 'Edit piece',
					'piece'      => $piece,
					'puzzle'		 => $treasurehuntPuzzle,
				)
			);
		}

		public function removePieceAction()
		{
			$service = $this->getAdminGameService();
			$pieceId = $this->getEvent()->getRouteMatch()->getParam('pieceId');
			if (!$pieceId) {
				return $this->redirect()->toRoute('admin/playgroundgame/list');
			}
			$piece   = $service->getTreasureHuntPieceMapper()->findById($pieceId);
			$treasurehuntId = $piece->getTreasureHunt()->getId();
			$service->getTreasureHuntPieceMapper()->remove($piece);
			$this->flashMessenger()->setNamespace('treasurehunt')->addMessage('The piece has been deleted');

			return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-piece-list', array('gameId'=>$treasurehuntId));
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
