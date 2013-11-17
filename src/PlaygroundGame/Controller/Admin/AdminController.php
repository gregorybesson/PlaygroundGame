<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Game;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundGame\Options\ModuleOptions;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class AdminController extends AbstractActionController
{
    protected $options;

    /**
     * @var GameService
     */
    protected $adminGameService;

    public function indexAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }

        $service = $this->getAdminGameService();
        $game = $service->getGameMapper()->findByIdentifier($identifier);

        if (!$game) {
            return $this->notFoundAction();
        }

        $viewModel = new ViewModel(
            array('game' => $game)
        );

        return $viewModel;
    }

    public function listAction()
    {
        $filter 	= $this->getEvent()->getRouteMatch()->getParam('filter');
        $type	= $this->getEvent()->getRouteMatch()->getParam('type');

        $service 	= $this->getAdminGameService();
		$adapter = new DoctrineAdapter(new ORMPaginator($service->getQueryGamesOrderBy($type, $filter)));
		$paginator = new Paginator($adapter);
		$paginator->setItemCountPerPage(25);
		$paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        foreach ($paginator as $game) {
            $game->entry = $service->getEntryMapper()->countByGame($game);
        }

        return array(
            'games' 	=> $paginator,
            'type' 		=> $type,
            'filter' 	=> $filter,
        );
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
            'game' => $game
        );
    }

    public function downloadAction()
    {
        // magically create $content as a string containing CSV data
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);
    
        $entries = $this->getAdminGameService()->getEntryMapper()->findBy(array('game' => $game,'winner' => 1));

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content       .= "ID;Pseudo;Nom;Prenom;E-mail;Optin partenaire;Eligible TAS ?\n";
        foreach ($entries as $e) {

            $content   .= $e->getUser()->getId()
                . ";" . $e->getUser()->getUsername()
                . ";" . $e->getUser()->getLastname()
                . ";" . $e->getUser()->getFirstname()
                . ";" . $e->getUser()->getEmail()
                . ";" . $e->getUser()->getOptinPartner()
                . ";" . $e->getWinner()
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

    public function removeAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $game = $service->getGameMapper()->findById($gameId);
        if ($game) {
            try {
                $service->getGameMapper()->remove($game);
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been edited');
            } catch (\Doctrine\DBAL\DBALException $e) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('Il y a déjà eu des participants à ce jeu. Vous ne pouvez plus le supprimer');
                //throw $e;
            }
        }

        return $this->redirect()->toRoute('admin/playgroundgame/list');
    }

    public function setActiveAction()
    {
        $service = $this->getAdminGameService();
        $gameId = (int)$this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        
        $game = $service->getGameMapper()->findById($gameId);
        $game->setActive(!$game->getActive());
        $service->getGameMapper()->update($game);

        return $this->redirect()->toRoute('admin/playgroundgame/list');
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceLocator()->get('playgroundgame_module_options'));
        }

        return $this->options;
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_game_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
