<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Game;

use PlaygroundGame\Entity\InstantWin;
use PlaygroundGame\Entity\InstantWinOccurrence;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class InstantWinController extends AbstractActionController
{

    /**
     * @var GameService
     */
    protected $adminGameService;

    public function removeAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $game = $service->getGameMapper()->findById($gameId);
        $service->getGameMapper()->remove($game);
        $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been edited');

        return $this->redirect()->toRoute('admin/playgroundgame/list');
    }

    public function createInstantWinAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/instant-win/instantwin');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/admin/game-form');

        $instantwin = new InstantWin();

        $form = $this->getServiceLocator()->get('playgroundgame_instantwin_form');
        $form->bind($instantwin);
        $form->get('submit')->setLabel('Add');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/create-instantwin', array('gameId' => 0)));
        $form->setAttribute('method', 'post');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_merge(
                    $this->getRequest()->getPost()->toArray(),
                    $this->getRequest()->getFiles()->toArray()
            );
            $game = $service->create($data, $instantwin, 'playgroundgame_instantwin_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(
        	array(
        		'form' => $form,
        		'title' => 'Create instant win',
			)
		);
    }

    public function editInstantWinAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');

        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/create-instantwin');
        }

        $game = $service->getGameMapper()->findById($gameId);
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/instant-win/instantwin');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/admin/game-form');

        $form   = $this->getServiceLocator()->get('playgroundgame_instantwin_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/edit-instantwin', array('gameId' => $gameId)));
        $form->setAttribute('method', 'post');
        $form->get('submit')->setLabel('Edit');
		
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
            $data = array_merge(
                    $this->getRequest()->getPost()->toArray(),
                    $this->getRequest()->getFiles()->toArray()
            );
            $result = $service->edit($data, $game, 'playgroundgame_instantwin_form');

            if ($result) {
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(array('form' => $form));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(
        	array(
        		'form' => $form,
        		'title' => 'Edit instant win',
			)
		);
    }

    public function listOccurrenceAction()
    {
        $service 	= $this->getAdminGameService();
        $gameId 	= $this->getEvent()->getRouteMatch()->getParam('gameId');
        $filter		= $this->getEvent()->getRouteMatch()->getParam('filter');

        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        //$instantwin = $service->getGameMapper()->findById($gameId);
		$game = $service->getGameMapper()->findById($gameId);
		
		$adapter = new DoctrineAdapter(new ORMPaginator($service->getInstantWinOccurrenceMapper()->queryByGame($game, array('occurrence_date' => $filter))));
		$paginator = new Paginator($adapter);
		$paginator->setItemCountPerPage(25);
		$paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'occurrences' => $paginator,
                'gameId' 	  => $gameId,
                'filter'	  => $filter,
                'game' 		  => $game,
            )
        );
    }

    public function addOccurrenceAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/instant-win/occurrence');
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $form = $this->getServiceLocator()->get('playgroundgame_instantwinoccurrence_form');
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/instantwin-occurrence-add', array('gameId' => $gameId)));
        $form->setAttribute('method', 'post');
        $form->get('instant_win_id')->setAttribute('value', $gameId);

        $occurrence = new InstantWinOccurrence();
        $form->bind($occurrence);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                    $this->getRequest()->getPost()->toArray(),
                    $this->getRequest()->getFiles()->toArray()
            );

            $occurrence = $service->createOccurrence($data);
            if ($occurrence) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The occurrence was created');

                return $this->redirect()->toRoute('admin/playgroundgame/instantwin-occurrence-list', array('gameId'=>$gameId));
            }
        }

        return $viewModel->setVariables(
            array(
                'form' => $form,
                'gameId' => $gameId,
                'occurrence_id' => 0,
                'title' => 'Add occurrence',
            )
        );
    }

    public function editOccurrenceAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/instant-win/occurrence');

        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        /*if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }*/

        $occurrenceId = $this->getEvent()->getRouteMatch()->getParam('occurrenceId');
        if (!$occurrenceId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $occurrence   = $service->getInstantWinOccurrenceMapper()->findById($occurrenceId);
        $instantwinId     = $occurrence->getInstantWin()->getId();

        $form = $this->getServiceLocator()->get('playgroundgame_instantwinoccurrence_form');
        $form->get('submit')->setAttribute('label', 'Add');
        $form->get('instant_win_id')->setAttribute('value', $instantwinId);

        $form->bind($occurrence);

        if ($this->getRequest()->isPost()) {
            $data = array_merge(
                    $this->getRequest()->getPost()->toArray(),
                    $this->getRequest()->getFiles()->toArray()
            );
            $occurrence = $service->updateOccurrence($data, $occurrence);
            if ($occurrence) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The occurrence was created');

                return $this->redirect()->toRoute('admin/playgroundgame/instantwin-occurrence-list', array('gameId'=>$instantwinId));
            }
        }

        return $viewModel->setVariables(
            array(
                'form' => $form,
                'gameId' => $instantwinId,
                'occurrence_id' => $occurrenceId,
                'title' => 'Edit occurrence',
                'gameId' => $gameId,
            )
        );
    }

    public function removeOccurrenceAction()
    {
        $service = $this->getAdminGameService();
        $occurrenceId = $this->getEvent()->getRouteMatch()->getParam('occurrenceId');
        if (!$occurrenceId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $occurrence   = $service->getInstantWinOccurrenceMapper()->findById($occurrenceId);
        $instantwinId = $occurrence->getInstantWin()->getId();
		
		if($occurrence->getActive()){
            $service->getInstantWinOccurrenceMapper()->remove($occurrence);
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The occurrence was created');
        } else {
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('Il y a un participant à cet instant gagnant. Vous ne pouvez plus le supprimer');
        }

        return $this->redirect()->toRoute('admin/playgroundgame/instantwin-occurrence-list', array('gameId'=>$instantwinId));
    }

    public function leaderboardAction()
    {
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        
        $adapter = new DoctrineAdapter(new ORMPaginator( $this->getAdminGameService()->getEntryMapper()->queryByGame($game)));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

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
        //$service        = $this->getLeaderBoardService();
        //$leaderboards   = $service->getLeaderBoardMapper()->findBy(array('game' => $game));

        $entries = $this->getAdminGameService()->getEntryMapper()->findBy(array('game' => $game,'winner' => 1));

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content       .= "ID;Pseudo;Civilité;Nom;Prénom;E-mail;Optin Metro;Optin partenaire;A Gagné ?;Date - H;Adresse;CP;Ville;Téléphone;Mobile;Date d'inscription;Date de naissance;\n";
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
            . "\n";
        }

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"leaderboard.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_instantwin_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
