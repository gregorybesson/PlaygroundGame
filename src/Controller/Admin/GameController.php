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
use Zend\Session\Container;

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
            'playgroundcore_admin_formgen',
            array(
                'controller' => 'playgroundcore_admin_formgen',
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
        // We try to get FB pages from the logged in user
        $session = new Container('facebook');
        $config = $this->getServiceLocator()->get('config');
        $appsArray = [];
        $platformFbAppId = '';
        
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
            $fbPage              = $config['facebook']['fb_page'];
        }
        $fb = new \Facebook\Facebook([
            'app_id' => $platformFbAppId,
            'app_secret' => $platformFbAppSecret,
            'default_graph_version' => 'v3.1',
            //'cookie' => false,
            //'default_access_token' => '{access-token}', // optional
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $fb_args_param = array('req_perms' => 'manage_pages,publish_pages');
        $fb_login_url = $helper->getLoginUrl($this->url()->fromRoute(
            'admin/playgroundgame/list',
            array(),
            array('force_canonical' => true)), $fb_args_param);
        $accessToken = $helper->getAccessToken();

        if (isset($accessToken) || $session->offsetExists('fb_token')) {
            if (isset($accessToken)) {
                $session->offsetSet('fb_token', $accessToken);
            }

            // checking if user access token is not valid then ask user to login again
            $debugToken = $fb->get('/debug_token?input_token='. $session->offsetGet('fb_token'), $platformFbAppId . '|' . $platformFbAppSecret)
            ->getGraphNode()
            ->asArray();
            if (isset($debugToken['error']['code'])) {
                $session->offsetUnset('fb_token');
            } else {
                // setting default user access token for future requests
                $fb->setDefaultAccessToken($session->offsetGet('fb_token'));
                $pages = $fb->get('/me/accounts')
                    ->getGraphEdge()
                    ->asArray();

                foreach ($pages as $key) {
                    $app_label = '';
                    if (isset($key['name'])) {
                        $app_label .= $key['name'];
                    }
                    if (isset($key['id'])) {
                        $app_label .= ' ('.$key['id'].')';
                    }
                    $appsArray[$key['id']] = $app_label;
                }
                $fb_login_url = '';

                if ($this->getRequest()->isPost()) {
                    $data = array_replace_recursive(
                        $this->getRequest()->getPost()->toArray(),
                        $this->getRequest()->getFiles()->toArray()
                    );
                    // Removing a previously page tab set on this game
                    if( 
                        $this->game && 
                        !empty($this->game->getFbPageId()) &&
                        !empty($this->game->getFbAppId()) &&
                        (   
                            $this->game->getFbPageId() !== $data['fbPageId'] ||
                            $this->game->getFbAppId() !== $data['fbAppId']
                        )
                    ) {
                        $oldPage = $fb->get('/' . $this->game->getFbPageId() . '?fields=access_token,name,id')
                            ->getGraphNode()
                            ->asArray();
                        $removeTab = $fb->delete(
                                '/' . $this->game->getFbPageId() . '/tabs',
                                [
                                    'tab' => 'app_'.$this->game->getFbAppId(),
                                ],
                                $oldPage['access_token']
                            )
                            ->getGraphNode()
                            ->asArray();
                    }
                }
            }
        }

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

        $pageIds = $form->get('fbPageId')->getOption('value_options');
        foreach($appsArray as $k => $v) {
            $pageIds[$k] = $v;
        }
        $form->get('fbPageId')->setAttribute('options', $pageIds);

        //if($form->get('fbAppId')->getValue() == '') {
            $form->get('fbAppId')->setValue($platformFbAppId);
        //}

        // if ($this->game->getFbAppId()) {
        //     $data['fbAppId'] = $form->get('fbAppId')->getOption('value_options');
        //     $appIds[$this->game->getFbAppId()] = $this->game->getFbAppId();
        //     $form->get('fbAppId')->setAttribute('options', $appIds);
        // }

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
            $game = $this->getAdminGameService()->createOrUpdate($data, $this->game, $formId);

            if ($game) {
                // Let's record the FB page tab if it is configured
                if ($session->offsetExists('fb_token')) {        
                    // adding page tab to selected page using page access token
                    if (!empty($data['fbPageId']) && !empty($data['fbAppId'])) {
                        $page = $fb->get('/' . $data['fbPageId'] . '?fields=access_token,name,id')
                        ->getGraphNode()
                        ->asArray();

                        try {
                            $addTab = $fb->post(
                                '/' . $page['id'] . '/tabs',
                                [
                                    'app_id' => $data['fbAppId'],
                                    'custom_name' => (!empty($data['fbPageTabTitle'])) ? $data['fbPageTabTitle'] : $data['title'],
                                    'custom_image_url' => ($game->getFbPageTabImage() !== '') ? 
                                        $this->getAdminGameService()->getServiceManager()->get('ViewRenderer')->url(
                                            'frontend',
                                            array(),
                                            array('force_canonical' => true)
                                        ).$game->getFbPageTabImage() :
                                        null,
                                    'position' => (!empty($data['fbPageTabPosition'])) ? $data['fbPageTabPosition'] : 99
                                ],
                                $page['access_token'])
                                ->getGraphNode()
                                ->asArray();
                        } catch (\Exception $e) {
                            // (#324) Missing or invalid image file
                            if($e->getCode() == '324') {
                                try {
                                    $addTab = $fb->post(
                                        '/' . $page['id'] . '/tabs',
                                        [
                                            'app_id' => $data['fbAppId'],
                                            'custom_name' => (!empty($data['fbPageTabTitle'])) ? $data['fbPageTabTitle'] : $data['title'],
                                            'position' => (!empty($data['fbPageTabPosition'])) ? $data['fbPageTabPosition'] : 99
                                        ],
                                        $page['access_token'])
                                        ->getGraphNode()
                                        ->asArray();
                                } catch (\Exception $e) {
                                    throw $e;
                                }
                            }
                        }
                    }
                }
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(
            array(
                'platform_fb_app_id' => $platformFbAppId,
                'fb_login_url' => $fb_login_url,
                'form' => $form,
                'game' => $this->game
            )
        );
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
        // We try to get FB pages from the logged in user
        $session = new Container('facebook');
        $config = $this->getServiceLocator()->get('config');
        
        if (isset($config['facebook'])) {
            $platformFbAppId     = $config['facebook']['fb_appid'];
            $platformFbAppSecret = $config['facebook']['fb_secret'];
            $fbPage              = $config['facebook']['fb_page'];
        }
        $fb = new \Facebook\Facebook([
            'app_id' => $platformFbAppId,
            'app_secret' => $platformFbAppSecret,
            'default_graph_version' => 'v3.1',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $accessToken = $helper->getAccessToken();

        if (isset($accessToken) || $session->offsetExists('fb_token')) {
            if (isset($accessToken)) {
                $session->offsetSet('fb_token', $accessToken);
            }

            // checking if user access token is not valid then ask user to login again
            $debugToken = $fb->get('/debug_token?input_token='. $session->offsetGet('fb_token'), $platformFbAppId . '|' . $platformFbAppSecret)
            ->getGraphNode()
            ->asArray();
            if (isset($debugToken['error']['code'])) {
                $session->offsetUnset('fb_token');
            } else {
                // setting default user access token for future requests
                $fb->setDefaultAccessToken($session->offsetGet('fb_token'));
            }
        }
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
