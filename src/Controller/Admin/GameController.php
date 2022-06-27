<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Service\Game as AdminGameService;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use PlaygroundGame\Options\ModuleOptions;
use Laminas\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use Laminas\Stdlib\ErrorHandler;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Session\Container;

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
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }

        $game = $this->getAdminGameService()->getGameMapper()->findById($gameId);
        if (!$game) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
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
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The form has been created');
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
        }
        $fb = new \Facebook\Facebook([
            'app_id' => $platformFbAppId,
            'app_secret' => $platformFbAppSecret,
            'default_graph_version' => 'v3.1',
        ]);

        $helper = $fb->getRedirectLoginHelper();
        $fb_args_param = array('req_perms' => 'manage_pages,publish_pages');
        $fb_login_url = $helper->getLoginUrl($this->adminUrl()->fromRoute(
            'playgroundgame/list',
            array(),
            array('force_canonical' => true)
        ), $fb_args_param);
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
                    if ($this->game &&
                        !empty($this->game->getFbPageId()) &&
                        !empty($this->game->getFbAppId()) &&
                        (
                            (
                                $this->game->getFbPageId() !== $data['fbPageId'] ||
                                $this->game->getFbAppId() !== $data['fbAppId']
                            ) ||
                            $data['broadcastFacebook'] == 0
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

                    // Removing a previously post set on this game
                    if ($this->game &&
                        !empty($this->game->getFbPostId()) &&
                        $data['broadcastPostFacebook'] == 0
                    ) {
                        $oldPage = $fb->get('/' . $this->game->getFbPageId() . '?fields=access_token,name,id')
                            ->getGraphNode()
                            ->asArray();
                        $removePost = $fb->delete(
                            '/' . $this->game->getFbPostId(),
                            [],
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
            $this->adminUrl()->fromRoute(
                'playgroundgame/edit-' . $this->game->getClassType(),
                array('gameId' => $this->game->getId())
            )
        );
        $form->setAttribute('method', 'post');

        $pageIds = $form->get('fbPageId')->getOption('value_options');
        foreach ($appsArray as $k => $v) {
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
                if ($session->offsetExists('fb_token')) {
                    if (!empty($data['fbPageId']) && !empty($data['fbAppId'])) {
                        $page = $fb->get('/' . $data['fbPageId'] . '?fields=access_token,name,id')
                        ->getGraphNode()
                        ->asArray();

                        // let's create a post on FB
                        if ($data['broadcastPostFacebook'] && $game->getWelcomeBlock() != '' && $game->getMainImage() != '') {
                            $imgPath = $this->url()->fromRoute('frontend', [], ['force_canonical' => true], false).$game->getMainImage();
                            // emoticons : $emoji = html_entity_decode('&#128520;');

                            $message = str_replace('<p>', "", $game->getWelcomeBlock());
                            $message = str_replace('</p>', "\n", $message);
                            $message = strip_tags($message);

                            // Create the post
                            try {
                                // Associate the fbAppId to the page so that we can receive the webhooks
                                $linkAppToPage = $fb->post(
                                    '/' . $page['id'] . '/subscribed_apps',
                                    array(),
                                    $page['access_token']
                                );

                                /**
                                 *  post text and save the post_id to be able to get the likes and comments on the post
                                 */
                                // $post = $fb->post(
                                //     '/' . $page['id'] . '/feed',
                                //     array(
                                //         'message'           => 'message',
                                //     ),
                                //     $page['access_token']
                                // );

                                /**
                                 * Post a photo
                                 */
                                // $post = $fb->post(
                                //     '/' . $page['id'] . '/photos',
                                //     array(
                                //         'url'           => 'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //         'published'     => true,
                                //     ),
                                //     $page['access_token']
                                // );

                                /**
                                 * Upload an unpublished photo and include it in a post
                                 */
                                $img = $fb->post(
                                    '/' . $page['id'] . '/photos',
                                    array(
                                        'url'           => $imgPath,
                                        'published'     => false,
                                    ),
                                    $page['access_token']
                                );
                                $img = $img->getGraphNode()->asArray();

                                if ($game->getFbPostId() != '') {
                                    $post = $fb->post(
                                        '/' . $game->getFbPostId(),
                                        array(
                                            'message'           => $message,
                                            'attached_media[0]' => '{"media_fbid":"'.$img['id'].'"}',
                                        ),
                                        $page['access_token']
                                    );
                                } else {
                                    $post = $fb->post(
                                        '/' . $page['id'] . '/feed',
                                        array(
                                            'message'           => $message,
                                            'attached_media[0]' => '{"media_fbid":"'.$img['id'].'"}',
                                        ),
                                        $page['access_token']
                                    );
                                }

                                /**
                                 * Upload an unpublished photo and include it in a scheduled post
                                 */
                                // $img = $fb->post(
                                //     '/' . $page['id'] . '/photos',
                                //     array(
                                //         'url'           => 'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //         'published'     => false,
                                //         'temporary'     => true
                                //     ),
                                //     $page['access_token']
                                // );
                                // $img = $img->getGraphNode()->asArray();
                                //
                                // $post = $fb->post(
                                //     '/' . $page['id'] . '/feed',
                                //     array(
                                //         'message'           => 'message avec image',
                                //         'attached_media[0]' => '{"media_fbid":"'.$img['id'].'"}',
                                //         'published'     => false,
                                //         'scheduled_publish_time' => '1512068400',
                                //         'unpublished_content_type' => 'SCHEDULED',
                                //     ),
                                //     $page['access_token']
                                // );

                                /**
                                 * publish multiple photos then associate these photos to a post
                                 */
                                // $endpoint = "/".$page['id']."/photos";
                                // $multiple_photos = [
                                //     'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //     'https://images.unsplash.com/photo-1538218952949-2f5dda4a9156?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=b79a9c7314dd5ca8eac2f187902ceca2&auto=format&fit=crop&w=2704&q=80',
                                //     'https://images.unsplash.com/photo-1538157245064-badfdabb7142?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=dfa50d5dd51b85f25ca03f2b2667752a&auto=format&fit=crop&w=2700&q=80',
                                // ];
                                // $photos = [];
                                // $data_post = ['attached_media' => [], 'message' => 'message', 'published' => true];
                                // foreach ($multiple_photos as $file_url):
                                //     array_push($photos, $fb->request('POST',$endpoint,['url' =>$file_url,'published' => false,'temporary' => true], $page['access_token']));
                                // endforeach;
                                // $uploaded_photos = $fb->sendBatchRequest($photos, $page['access_token']);
                                // $uploaded_photos = $uploaded_photos->getGraphNode()->asArray();

                                // foreach ($uploaded_photos as $photo):
                                //     $photo = json_decode($photo['body']);
                                //     array_push($data_post['attached_media'], '{"media_fbid":"'.$photo->id.'"}');
                                // endforeach;
                                // $post = $fb->sendRequest('POST', "/".$page['id']."/feed", $data_post, $page['access_token']);

                                /**
                                 * publish a carrousel to a post
                                 */
                                // $data_post = [
                                //     'child_attachments' => [],
                                //     'message' => 'message',
                                //     'link' => 'https://www.playground.gg',
                                //     'multi_share_end_card' => false,
                                //     'published' => true,
                                // ];

                                // $multiple_photos = [
                                //     'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //     'https://images.unsplash.com/photo-1538218952949-2f5dda4a9156?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=b79a9c7314dd5ca8eac2f187902ceca2&auto=format&fit=crop&w=2704&q=80',
                                //     'https://images.unsplash.com/photo-1538157245064-badfdabb7142?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=dfa50d5dd51b85f25ca03f2b2667752a&auto=format&fit=crop&w=2700&q=80',
                                // ];
                                // foreach ($multiple_photos as $k => $photo):
                                //     array_push($data_post['child_attachments'], '{"link":"'.$photo.'", "name": "message_'.$k.'"}');
                                // endforeach;
                                // $post = $fb->sendRequest('POST', "/".$page['id']."/feed", $data_post, $page['access_token']);

                                /** Texte avec lien vers une page
                                 *
                                 */
                                // $post = $fb->post(
                                //     '/' . $page['id'] . '/feed',
                                //     array(
                                //         'message'           => 'message',
                                //         'link'              => 'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //         //'picture'           => 'https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80',
                                //         'call_to_action'    => '{"type":"BOOK_TRAVEL","value":{"link":"https://images.unsplash.com/photo-1538239010247-383da61e35db?ixlib=rb-0.3.5&ixid=eyJhcHBfaWQiOjEyMDd9&s=22e9de10cd7e4d8e32d698099dc6d23c&auto=format&fit=crop&w=3289&q=80"}}',
                                //     ),
                                //     $page['access_token']
                                // );
                            } catch (\Exception $e) {
                                if ($e->getMessage() == 'Missing or invalid image file') {
                                    if ($game->getFbPostId() != '') {
                                        $post = $fb->post(
                                            '/' . $game->getFbPostId(),
                                            array(
                                                'message' => $message,
                                            ),
                                            $page['access_token']
                                        );
                                    } else {
                                        $post = $fb->post(
                                            '/' . $page['id'] . '/feed',
                                            array(
                                                'message' => $message,
                                            ),
                                            $page['access_token']
                                        );
                                    }
                                } else {
                                    throw $e;
                                }
                            }
                            $post = $post->getGraphNode()->asArray();
                            if (isset($post['id'])) {
                                $game->setFbPostId($post['id']);
                                $game = $this->getAdminGameService()->getGameMapper()->update($game);
                            }
                        }

                        // Let's record the FB page tab if it is configured
                        // adding page tab to selected page using page access token
                        if ($data['broadcastFacebook']) {
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
                                    $page['access_token']
                                )
                                    ->getGraphNode()
                                    ->asArray();
                            } catch (\Exception $e) {
                                // (#324) Missing or invalid image file
                                if ($e->getCode() == '324') {
                                    try {
                                        $addTab = $fb->post(
                                            '/' . $page['id'] . '/tabs',
                                            [
                                                'app_id' => $data['fbAppId'],
                                                'custom_name' => (!empty($data['fbPageTabTitle'])) ? $data['fbPageTabTitle'] : $data['title'],
                                                'position' => (!empty($data['fbPageTabPosition'])) ? $data['fbPageTabPosition'] : 99
                                            ],
                                            $page['access_token']
                                        )
                                            ->getGraphNode()
                                            ->asArray();
                                    } catch (\Exception $e) {
                                        throw $e;
                                    }
                                }
                            }
                        }
                    }
                }
                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
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
            $fb = new \Facebook\Facebook([
                'app_id' => $platformFbAppId,
                'app_secret' => $platformFbAppSecret,
                'default_graph_version' => 'v3.1',
            ]);

            $helper = $fb->getRedirectLoginHelper();
            $accessToken = $helper->getAccessToken();
        }

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

    /**
     * Return the list of entries for the game
     *
     * @return void
     */
    public function entryAction()
    {
        $this->checkGame();

        $navigation = $this->getServiceLocator()->get('ViewHelperManager')->get('navigation');
        $page = $navigation('admin_navigation')->findOneBy('route', 'admin/playgroundgame/edit-' . $this->game->getClassType());
        $page->setParams(['gameId' => $this->game->getId()]);
        $page->setLabel($this->game->getTitle());

        $grid = $this->getAdminGameService()->getGrid($this->game);
        $grid->render();

        return $grid->getResponse();
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

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute($this->game->getClassType() .'/invitation', array('gameId'=>$this->game->getId())));
    }

    public function downloadAction()
    {
        $this->checkGame();
        $header = $this->getAdminGameService()->getEntriesHeader($this->game);
        $qb = $this->getAdminGameService()->getEntriesQuery($this->game);
        $query = $qb->getQuery();

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
     * @return \Laminas\Stdlib\ResponseInterface
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
        $form->setAttribute('action', $this->adminUrl()->fromRoute('playgroundgame/import'));
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

            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
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

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
    }

    public function setActiveAction()
    {
        $this->checkGame();

        $this->game->setActive(!$this->game->getActive());
        $this->getAdminGameService()->getGameMapper()->update($this->game);

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
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
