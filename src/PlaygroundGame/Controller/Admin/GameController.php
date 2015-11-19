<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundGame\Options\ModuleOptions;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Stdlib\ErrorHandler;

class GameController extends AbstractActionController
{
    protected $options;

    /**
     * @var GameService
     */
    protected $adminGameService;

    public function listAction()
    {
        $filter    = $this->getEvent()->getRouteMatch()->getParam('filter');
        $type    = $this->getEvent()->getRouteMatch()->getParam('type');

        $service    = $this->getAdminGameService();
        $adapter = new DoctrineAdapter(new ORMPaginator($service->getQueryGamesOrderBy($type, $filter)));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        foreach ($paginator as $game) {
            $game->entry = $service->getEntryMapper()->countByGame($game);
        }

        return array(
            'games'    => $paginator,
            'type'        => $type,
            'filter'    => $filter,
        );
    }

    public function entryAction()
    {
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $game = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        $adapter = new DoctrineAdapter(new ORMPaginator($this->getAdminGameService()->getEntryMapper()->queryByGame($game)));
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return array(
            'entries' => $paginator,
            'game' => $game,
            'gameId' => $gameId
        );
    }

    // Used for Lottery, TreasureHunt and redifined for Quiz and InstantWin because it's slightly different
    public function downloadAction()
    {
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $content = $this->getAdminGameService()->download($gameId);

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

    // Only used for Quiz and Lottery
    public function drawAction()
    {
        // magically create $content as a string containing CSV data
        $gameId         = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $game           = $this->getAdminGameService()->getGameMapper()->findById($gameId);

        $winningEntries = $this->getAdminGameService()->draw($game);

        $content        = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content       .= "ID;Pseudo;Nom;Prenom;E-mail;Etat\n";

        foreach ($winningEntries as $e) {
            $etat = 'gagnant';

            $content   .= $e->getUser()->getId()
            . ";" . $e->getUser()->getUsername()
            . ";" . $e->getUser()->getLastname()
            . ";" . $e->getUser()->getFirstname()
            . ";" . $e->getUser()->getEmail()
            . ";" . $etat
            ."\n";
        }

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"gagnants.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }
    
    /**
     * This method serialize a game an export it as a txt file
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function exportAction()
    {
        // magically create $content as a string containing CSV data
        $gameId  = $this->getEvent()->getRouteMatch()->getParam('gameId');
        $game    = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        $content = serialize($game);

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"". $game->getIdentifier() .".txt\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));
    
        $response->setContent($content);
    
        return $response;
    }
    
    /**
     * This method take an uploaded txt file containing a serialized game
     * and persist it in the database
     * @return unknown
     */
    public function importAction()
    {
        $form = $this->getServiceLocator()->get('playgroundgame_import_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/import'));
        $form->setAttribute('method', 'post');
        
        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            
            if (! empty($data['import_file']['tmp_name'])) {
                ErrorHandler::start();
                $game = unserialize(file_get_contents($data['import_file']['tmp_name']));
                ErrorHandler::stop(true);
            }
            $game->setId(null);
            if ($data['slug']) {
                $game->setIdentifier($data['slug']);
            }
            
            $duplicate = $this->getAdminGameService()->getGameMapper()->findByIdentifier($game->getIdentifier());
            if (!$duplicate) {
                $this->getAdminGameService()->getGameMapper()->insert($game);
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        
        return array(
            'form' => $form,
        );
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

    public function formAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $game = $service->getGameMapper()->findById($gameId);
        $form = $service->getPlayerFormMapper()->findOneBy(array('game' => $game));

        // I use the wonderful Form Generator to create the Post & Vote form
        $this->forward()->dispatch('PlaygroundCore\Controller\Formgen', array('controller' => 'PlaygroundCore\Controller\Formgen', 'action' => 'create'));

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form = $service->createForm($data, $game, $form);
            if ($form) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The form was created');
            }
        }
        $formTemplate='';
        if ($form) {
            $formTemplate = $form->getFormTemplate();
        }

        return array(
            'form' => $form,
            'formTemplate' => $formTemplate,
            'gameId' => $gameId,
            'game' => $game,
        );
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
