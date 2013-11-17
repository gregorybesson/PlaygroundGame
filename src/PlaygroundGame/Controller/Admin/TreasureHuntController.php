<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Game;

use PlaygroundGame\Entity\TreasureHunt;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class TreasureHuntController extends AbstractActionController
{
    /**
     * @var GameService
     */
    protected $adminGameService;

    public function createTreasureHuntAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/treasure-hunt/treasurehunt');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/admin/game-form');

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
            $game = $service->create($data, $treasurehunt, 'playgroundgame_treasurehunt_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $treasurehunt));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create treasurehunt'));
    }
    
    public function areapickerAction()
    {
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	$viewModel->setTemplate('playground-game/treasure-hunt/areapicker');
    	return $viewModel;
    }

    public function editTreasureHuntAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');

        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/createTreasureHunt');
        }

        $game = $service->getGameMapper()->findById($gameId);
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/treasure-hunt/treasurehunt');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/admin/game-form');

        $form   = $this->getServiceLocator()->get('playgroundgame_treasurehunt_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/edit-treasurehunt', array('gameId' => $gameId)));
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
            if(empty($data['prizes'])){
                $data['prizes'] = array();
            }
            
            $result = $service->edit($data, $game, 'playgroundgame_treasurehunt_form');

            if ($result) {
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(array('form' => $form, 'game' => $game));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Edit treasurehunt'));
    }


    public function listPuzzleAction()
    {
    	$service 	= $this->getAdminGameService();
    	$gameId 	= $this->getEvent()->getRouteMatch()->getParam('gameId');
    	$filter		= $this->getEvent()->getRouteMatch()->getParam('filter');
    
    	if (!$gameId) {
    		return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}
    
    	//$treasurehunt = $service->getGameMapper()->findById($gameId);
    	$puzzles = $service->getTreasureHuntPuzzleMapper()->findByGameId($gameId);
    	$game = $service->getGameMapper()->findById($gameId);
    
    	if (is_array($puzzles)) {
    		$paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($puzzles));
    		$paginator->setItemCountPerPage(50);
    		$paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
    	} else {
    		$paginator = $puzzles;
    	}
    
    	return new ViewModel(
    			array(
    					'puzzles' => $paginator,
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
    
    	$puzzle = new \PlaygroundGame\Entity\TreasureHuntPuzzle();
    	$form->bind($puzzle);
    
    	if ($this->getRequest()->isPost()) {
    		$data = array_merge(
    			$this->getRequest()->getPost()->toArray(),
    			$this->getRequest()->getFiles()->toArray()
    		);
    
    		$puzzle = $service->createPuzzle($data);
    		if ($puzzle) {
    			// Redirect to list of games
    			$this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The puzzle was created');
    
    			return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-list', array('gameId'=>$gameId));
    		}
    	}
    
    	return $viewModel->setVariables(
    		array(
    			'form' => $form,
   				'gameId' => $gameId,
   				'puzzle_id' => 0,
   				'title' => 'Add puzzle',
    		)
    	);
    }
    
    public function editPuzzleAction()
    {
    	$service = $this->getAdminGameService();
    	$viewModel = new ViewModel();
    	$viewModel->setTemplate('playground-game/treasure-hunt/puzzle');
    
    	$gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
    	/*if (!$gameId) {
    	 return $this->redirect()->toRoute('admin/playgroundgame/list');
    	}*/
    
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
    			// Redirect to list of games
    			$this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The puzzle was updated');
    
    			return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-list', array('gameId'=>$treasurehuntId));
    		}
    	}
    
    	return $viewModel->setVariables(
    		array(
    			'form' => $form,
    			'gameId' => $treasurehuntId,
   				'puzzle_id' => $puzzleId,
   				'title' => 'Edit puzzle',
   				'gameId' => $gameId,
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
    	$this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The puzzle was deleted');
    
    	return $this->redirect()->toRoute('admin/playgroundgame/treasurehunt-puzzle-list', array('gameId'=>$treasurehuntId));
    }
    
    public function entryAction()
    {
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);

        $entries = $this->getAdminGameService()->getEntryMapper()->findBy(array('game' => $game));

        if (is_array($entries)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($entries));
            $paginator->setItemCountPerPage(10);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $entries;
        }

        return array(
                'entries' => $paginator,
                'game' => $game,
                'gameId' => $gameId
        );
    }

    public function downloadAction()
    {
        // magically create $content as a string containing CSV data
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);

        $entries = $this->getAdminGameService()->getEntryMapper()->findBy(array('game' => $game,'winner' => 1));

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content       .= "ID;Pseudo;Civilité;Nom;Prénom;E-mail;Optin Newsletter;Optin partenaire;Eligible TAS ?;Date - H;Adresse;CP;Ville;Téléphone;Mobile;Date d'inscription;Date de naissance;\n";
        foreach ($entries as $e) {
        	if($e->getUser()->getAddress2() != '') {
        		$adress2 = ' - ' . $e->getUser()->getAddress2();
			} else {
				$adress2 = '';
			}
			if($e->getUser()->getDob() != NULL) {
				$dob = $e->getUser()->getDob()->format('Y-m-d');
			} else {
				$dob = '';
			}
			
            $content   .= $e->getUser()->getId()
                . ";" . $e->getUser()->getUsername()
				. ";" . $e->getUser()->getTitle()
                . ";" . $e->getUser()->getLastname()
                . ";" . $e->getUser()->getFirstname()
                . ";" . $e->getUser()->getEmail()
            	. ";" . $e->getUser()->getOptin()
                . ";" . $e->getUser()->getOptinPartner()
                . ";" . $e->getWinner()
				. ";" . $e->getCreatedAt()->format('Y-m-d H:i:s')
				. ";" . $e->getUser()->getAddress() . $adress2
				. ";" . $e->getUser()->getPostalCode()
				. ";" . $e->getUser()->getCity()
				. ";" . $e->getUser()->getTelephone()
				. ";" . $e->getUser()->getMobile()
				. ";" . $e->getUser()->getCreatedAt()->format('Y-m-d')
				. ";" . $dob
                ."\n";
        }

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"entry.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
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
