<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PlaygroundGame\Options\ModuleOptions;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Zend\Stdlib\ErrorHandler;
use Zend\ServiceManager\ServiceLocatorInterface;

class GameController extends AbstractActionController
{
    protected $options;

    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;

    protected $game;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function checkGame()
    {
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');
        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        
        $game = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        if (!$game) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $this->game = $game;
    }

    public function createForm($form)
    {
        // I use the wonderful Form Generator to create the Player form
        $this->forward()->dispatch(
            'PlaygroundCore\Controller\Formgen',
            array(
                'controller' => 'PlaygroundCore\Controller\Formgen',
                'action' => 'create'
            )
        );

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form = $this->getAdminGameService()->createForm($data, $this->game, $form);
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
            'gameId' => $this->game->getId(),
            'game' => $this->game,
        );
    }

    /**
     * @param string $templatePath
     * @param string $formId
     */
    public function editGame($templatePath, $formId)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($templatePath);

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $form   = $this->getServiceLocator()->get($formId);
        $form->setAttribute(
            'action',
            $this->url()->fromRoute(
                'admin/playgroundgame/edit-' . $this->game->getClassType(),
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');

        if ($this->game->getFbAppId()) {
            $appIds = $form->get('fbAppId')->getOption('value_options');
            $appIds[$this->game->getFbAppId()] = $this->game->getFbAppId();
            $form->get('fbAppId')->setAttribute('options', $appIds);
        }

        $gameOptions = $this->getAdminGameService()->getOptions();
        $gameStylesheet = $gameOptions->getMediaPath() . '/' . 'stylesheet_'. $this->game->getId(). '.css';
        if (is_file($gameStylesheet)) {
            $values = $form->get('stylesheet')->getValueOptions();
            $values[$gameStylesheet] = 'Style personnalisé de ce jeu';

            $form->get('stylesheet')->setAttribute('options', $values);
        }

        $form->bind($this->game);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }
            if (isset($data['drawDate']) && $data['drawDate']) {
                $data['drawDate'] = \DateTime::createFromFormat('d/m/Y', $data['drawDate']);
            }
            $result = $this->getAdminGameService()->createOrUpdate($data, $this->game, $formId);

            if ($result) {
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(array('form' => $form, 'game' => $this->game));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(
            array(
                'form' => $form,
                'title' => 'Edit this game',
            )
        );
    }

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
        $this->checkGame();

        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getAdminGameService()->getEntriesQuery($this->game)
            )
        );
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        $header = $this->getAdminGameService()->getEntriesHeader($this->game);
        $entries = $this->getAdminGameService()->getGameEntries($header, $paginator, $this->game);

        return array(
            'paginator' => $paginator,
            'entries' => $entries,
            'header' => $header,
            'game' => $this->game,
            'gameId' => $this->game->getId()
        );
    }

    public function invitationAction()
    {
        $this->checkGame();

        $adapter = new DoctrineAdapter(
            new LargeTablePaginator(
                $this->getAdminGameService()->getInvitationMapper()->queryByGame($this->game)
            )
        );
        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(25);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return new ViewModel(
            array(
                'invitations' => $paginator,
                'gameId'      => $this->game->getId(),
                'game'        => $this->game,
            )
        );
    }

    public function removeInvitationAction()
    {
        $this->checkGame();

        $service = $this->getAdminGameService();
        $invitationId = $this->getEvent()->getRouteMatch()->getParam('invitationId');
        if ($invitationId) {
            $invitation   = $service->getInvitationMapper()->findById($invitationId);
            $service->getInvitationMapper()->remove($invitation);
        }

        return $this->redirect()->toRoute(
            'admin/'. $this->game->getClassType() .'/invitation',
            array('gameId'=>$this->game->getId())
        );
    }
    
    public function downloadAction()
    {
        $this->checkGame();
        $header = $this->getAdminGameService()->getEntriesHeader($this->game);
        $query = $this->getAdminGameService()->getEntriesQuery($this->game);

        $content = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content .= $this->getAdminGameService()->getCSV(
            $this->getAdminGameService()->getGameEntries(
                $header,
                $query->getResult(),
                $this->game
            )
        );

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
        $this->checkGame();

        $winningEntries = $this->getAdminGameService()->draw($this->game);

        $content = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content .= "ID;Pseudo;Nom;Prenom;E-mail;Etat\n";

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
        $this->checkGame();
        $content = serialize($this->game);

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/plain; charset=UTF-8');
        $headers->addHeaderLine(
            'Content-Disposition',
            "attachment; filename=\"". $this->game->getIdentifier() .".txt\""
        );
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
                $game->setId(null);
                if ($data['slug']) {
                    $game->setIdentifier($data['slug']);
                }
                $duplicate = $this->getAdminGameService()->getGameMapper()->findByIdentifier($game->getIdentifier());
                if (!$duplicate) {
                    $this->getAdminGameService()->getGameMapper()->insert($game);
                }

                ErrorHandler::stop(true);
            }
            
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        
        return array(
            'form' => $form,
        );
    }

    public function removeAction()
    {
        $this->checkGame();

        try {
            $this->getAdminGameService()->getGameMapper()->remove($this->game);
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game has been edited');
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->flashMessenger()->setNamespace('playgroundgame')->addMessage(
                'Il y a déjà eu des participants à ce jeu. Vous ne pouvez plus le supprimer'
            );
        }

        return $this->redirect()->toRoute('admin/playgroundgame/list');
    }

    public function setActiveAction()
    {
        $this->checkGame();

        $this->game->setActive(!$this->game->getActive());
        $this->getAdminGameService()->getGameMapper()->update($this->game);

        return $this->redirect()->toRoute('admin/playgroundgame/list');
    }

    public function formAction()
    {
        $this->checkGame();
        
        $form = $this->game->getPlayerForm();

        return $this->createForm($form);
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
