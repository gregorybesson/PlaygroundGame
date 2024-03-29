<?php

namespace PlaygroundGame\Controller\Frontend;

use Laminas\Form\Form;
use Laminas\ServiceManager\ServiceLocatorInterface;

class PostVoteController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    /**
     * --DONE-- 1. try to change the Game Id (on le redirige vers la home du jeu)
     * --DONE-- 2. try to modify questions (the form is recreated and verified in the controller)
     * --DONE-- 3. don't answer to questions (form is checked controller side)
     * 4. try to game the chrono
     * 5. try to play again
     * 6. try to change answers
     *  --DONE-- 7. essaie de répondre sans être inscrit (on le redirige vers la home du jeu)
     */
    public function playAction()
    {
        $entry = $this->getGameService()->play($this->game, $this->user);

        if (!$entry) {
            $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
            if ($lastEntry === null) {
                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        'postvote',
                        array('id' => $this->game->getIdentifier())
                    )
                );
            }

            $lastEntryId = $lastEntry->getId();
            $lastPost = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntryId));
            $postId = $lastPost->getId();
            if ($lastPost->getStatus() == 2) {
                // the user has already taken part to this game and the participation limit has been reached
                $this->flashMessenger()->addMessage(
                    $this->getServiceLocator()->get('MvcTranslator')->translate('You have already a Post')
                );

                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        'postvote/post',
                        array(
                            'id' => $this->game->getIdentifier(),
                            'post' => $postId,
                        )
                    )
                );
            } else {
                $this->flashMessenger()->addMessage(
                    $this->getServiceLocator()->get('MvcTranslator')->translate('Your Post is waiting for validation')
                );

                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        'postvote/post',
                        array(
                            'id' => $this->game->getIdentifier(),
                            'post' => $postId,

                        )
                    )
                );
            }
        }

        if (! $this->game->getForm()) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $form = $this->getGameService()->createFormFromJson($this->game->getForm()->getForm(), 'postvoteForm');

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));
        if ($post) {
            foreach ($post->getPostElements() as $element) {
                try {
                    $form->get($element->getName())->setValue($element->getValue());

                    $elementType = $form->get($element->getName())->getAttribute('type');
                    if ($elementType == 'file' && $element->getValue() != '') {
                        $filter = $form->getInputFilter();
                        $elementInput = $filter->get($element->getName());
                        $elementInput->setRequired(false);
                        $form->get($element->getName())->setAttribute('required', false);
                    }
                } catch (\Laminas\Form\Exception\InvalidElementException $e) {
                }
            }
        }

        $viewModel = $this->buildView($this->game);

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                $post = $this->getGameService()->createPost($data, $this->game, $this->user, $form);

                if ($post && !empty($this->game->nextStep('play'))) {
                    // determine the route where the user should go
                    $redirectUrl = $this->frontendUrl()->fromRoute(
                        'postvote/'.$this->game->nextStep('play'),
                        array('id' => $this->game->getIdentifier())
                    );

                    return $this->redirect()->toUrl($redirectUrl);
                }
            } else {
                $messages = $form->getMessages();
                $viewModel = $this->buildView($this->game);
                $viewModel->setVariables(array(
                    'success' => false,
                    'message' => (is_array($messages['title'])) ? implode(',', $messages['title']) : $messages['title'],
                ));
            }
        }

        $viewModel->setVariables(
            array(
                'playerData' => $entry->getPlayerData(),
                'form' => $form,
                'post' => $post,
            )
        );

        return $viewModel;
    }

    public function previewAction()
    {
        $entry = $this->getGameService()->findLastActiveEntry($this->game, $this->user);

        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote/'.$this->game->nextStep('preview'),
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));

        if (! $post) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getGameService()->confirmPost($this->game, $this->user);

            if ($post) {
                if (!($step = $this->game->nextStep('preview'))) {
                    $step = 'result';
                }
                $redirectUrl = $this->frontendUrl()->fromRoute(
                    'postvote/'.$step,
                    array('id' => $this->game->getIdentifier())
                );

                // If a redirect is asked after the preview.
                if ($this->getRequest()->getQuery()->get('redirect') != '') {
                    $redirectUrl = $this->getRequest()->getQuery()->get('redirect');
                }

                return $this->redirect()->toUrl($redirectUrl);
            }
        }

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array('post' => $post));

        return $viewModel;
    }

    /**
     * View the Post page
     * @return multitype:|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function postAction()
    {
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $voted = false;
        $statusMail = false;
        $mailService = $this->getServiceLocator()->get('playgroundgame_message');
        $to = '';
        $skinUrl = $this->getGameService()->getServiceManager()->get('ViewRenderer')->url(
            'frontend',
            array(),
            array('force_canonical' => true)
        );
        $config = $this->getGameService()->getServiceManager()->get('config');
        if (isset($config['moderation']['email'])) {
            $to = $config['moderation']['email'];
        }

        if (! $postId) {
            return $this->notFoundAction();
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $this->getGameService()->getPostVotePostMapper()->findById($postId);

        if (! $post || $post->getStatus() === 9) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }
        $this->getGameService()->addView(
            $this->user,
            $this->getRequest()->getServer('REMOTE_ADDR'),
            $post
        );

        $formModeration = new Form();
        $formModeration->setAttribute('method', 'post');

        $formModeration->add(
            array(
                'name' => 'moderation',
                'attributes' => array(
                    'type' => 'hidden',
                    'value' => '1'
                ),
            )
        );

        $form = new \PlaygroundGame\Form\Frontend\PostVoteVote(
            $this->frontendUrl()->fromRoute(
                'postvote/post/captcha',
                array(
                    'id' => $this->game->getIdentifier(),
                    'post' => $postId,
                )
            )
        );

        if ($this->user) {
            $form->remove('captcha');
            if (count($this->getGameService()->getPostvoteVoteMapper()->findBy(array('user' => $this->user, 'post' => $post, 'postComment' => null))) > 0) {
                $voted = true;
            }
        }

        $alreadyVoted = '';
        $reportId = '';

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            if (isset($data['moderation'])) {
                $formModeration->setData($data);
                if ($formModeration->isValid()) {
                    $from = $to;
                    $subject= 'Moderation Post and Vote';
                    $result = $mailService->createHtmlMessage(
                        $from,
                        $to,
                        $subject,
                        'playground-game/email/moderation',
                        array('data' => $data, 'skinUrl' => $skinUrl)
                    );
                    $mailService->send($result);
                    if ($result) {
                        $statusMail = true;
                        $reportId = $data['reportid'];
                    }
                }
            } else {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    if ($this->getGameService()->addVote(
                        $this->user,
                        $this->getRequest()->getServer('REMOTE_ADDR'),
                        $post
                    )) {
                        $voted = true;
                    } else {
                        $alreadyVoted = 'Vous avez déjà voté!';
                    }
                }
            }
        }

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(
            array(
                'post'  => $post,
                'voted' => $voted,
                'form'  => $form,
                'formModeration' => $formModeration,
                'statusMail' => $statusMail,
                'alreadyVoted' => $alreadyVoted,
                'reportId' => $reportId,
            )
        );

        return $viewModel;
    }

    /**
     *
     */
    public function resultAction()
    {
        $playLimitReached = false;
        if ($this->getRequest()->getQuery()->get('playLimitReached')) {
            $playLimitReached = true;
        }
        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
        if ($lastEntry === null) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier)));
        }
        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntry));

        if (! $post) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        // DEPRECATED: we should be able to add a specific view to each action
        // based on config in the admin (tab design)
        // $view = $this->forward()->dispatch(
        //     'playgroundgame_'.$this->game->getClassType(),
        //     array(
        //         'controller' => 'playgroundgame_'.$this->game->getClassType(),
        //         'action' => 'share',
        //         'id' => $this->game->getIdentifier()
        //     )
        // );

        // if ($view && $view instanceof \Laminas\View\Model\ViewModel) {
        //     $view->setVariables(array('post' => $post));

        //     return $view;
        // } elseif ($view && $view instanceof \Laminas\Http\PhpEnvironment\Response) {
        //     return $view;
        // } else {
        $form = $this->getServiceLocator()
            ->get('playgroundgame_sharemail_form')
            ->setAttribute('method', 'post');

        $viewModel = $this->buildView($this->game);

        if (!$playLimitReached) {
            $this->getGameService()->sendMail($this->game, $this->user, $lastEntry);
        }

        $viewModel->setVariables(
            [
                'statusMail' => null,
                'post' => $post,
                'form' => $form,
                'playLimitReached' => $playLimitReached,
            ]
        );

        return $viewModel;
        //}
    }

    /**
     * Example of AJAX File Upload with Session Progress and partial validation.
     * It's now possible to send a base64 image in this case the call is the form :
     * this._ajax(
     * {
     *   url: url.dataset.url,
     *    method: 'post',
     *    body: 'photo=' + image
     * },
     *
     * @return \Laminas\Stdlib\ResponseInterface
     */
    public function ajaxuploadAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();

        if (! $this->game) {
            $this->getResponse()->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $this->getResponse();
        }

        $entry = $this->getGameService()->findLastActiveEntry(
            $this->game,
            $this->user
        );
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->getResponse()->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $this->getResponse();
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getFiles()->toArray();

            if (empty($data)) {
                $data = $this->getRequest()->getPost()->toArray();
                $key = key($data);
                $uploadImage = array('name' => $key.'.png', 'error' => 0, 'base64' => $data[$key]);
                $data = array($key => $uploadImage);
            }
            $uploadFile = $this->getGameService()->uploadFileToPost(
                $data,
                $this->game,
                $this->user
            );
        }

        $this->getResponse()->setContent(\Laminas\Json\Json::encode(array(
            'success' => true,
            'fileUrl' => $uploadFile
        )));

        return $this->getResponse();
    }

    public function ajaxdeleteAction()
    {
        $response = $this->getResponse();

        if (! $this->game) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        $entry = $this->getGameService()->findLastActiveEntry($this->game, $this->user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }
        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $this->getGameService()->deleteFilePosted($data, $this->game, $this->user);
        }

        $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => true,
        )));

        return $response;
    }

    public function ajaxrejectPostAction()
    {
        $response = $this->getResponse();
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');

        if (! $postId) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        $post = $this->getGameService()->getPostVotePostMapper()->findById($postId);

        if (! $post || $post->getUser()->getId() !== $this->user->getId()) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if ($this->getRequest()->isPost()) {
            $postvotePostMapper = $this->getGameService()->getPostVotePostMapper();
            // Post is rejected by User
            $post->setStatus(8);
            $postvotePostMapper->update($post);
        }

        $response->setContent(\Laminas\Json\Json::encode(array(
            'success' => true,
        )));

        return $response;
    }

    public function listAction()
    {
        $filter = $this->getEvent()->getRouteMatch()->getParam('filter');
        $search = $this->params()->fromQuery('name');
        $postId = $this->params()->fromQuery('id');
        $statusMail = false;
        $mailService = $this->getServiceLocator()->get('playgroundgame_message');
        $to = '';
        $skinUrl = $this->getGameService()->getServiceManager()->get('ViewRenderer')->url(
            'frontend',
            array(),
            array('force_canonical' => true)
        );
        $config = $this->getGameService()->getServiceManager()->get('config');
        if (isset($config['moderation']['email'])) {
            $to = $config['moderation']['email'];
        }

        $request = $this->getRequest();

        // Je recherche les posts validés associés à ce jeu
        $posts = $this->getGameService()->findArrayOfValidatedPosts($this->game, $this->user, $filter, $search);

        if (is_array($posts)) {
            $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($posts));
            $paginator->setItemCountPerPage($this->game->getPostDisplayNumber());
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $posts;
        }

        $form = new Form();
        $form->setAttribute('method', 'post');

        $form->add(array(
            'name' => 'moderation',
            'attributes' => array(
                'type' => 'hidden',
                'value' => '1'
            ),
        ));

        $reportId ='';

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();

            if (isset($data['moderation'])) {
                $from = $to;
                $subject= 'Moderation Post and Vote';
                $result = $mailService->createHtmlMessage(
                    $from,
                    $to,
                    $subject,
                    'playground-game/email/moderation',
                    array('data' => $data, 'skinUrl' => $skinUrl)
                );
                $mailService->send($result);
                if ($result) {
                    $statusMail = true;
                    $reportId = $data['reportid'];
                }
            }
        }

        $viewModel = $this->buildView($this->game);

        if ($postId) {
            $postTarget = $this->getGameService()->getPostVotePostMapper()->findById($postId);
            if ($postTarget) {
                foreach ($postTarget->getPostElements() as $element) {
                    $fbShareImage = $this->frontendUrl()->fromRoute(
                        '',
                        array(),
                        array('force_canonical' => true),
                        false
                    ) . $element->getValue();
                    break;
                }

                $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));

                // Without bit.ly shortener
                $socialLinkUrl = $this->frontendUrl()->fromRoute(
                    'postvote/list',
                    array(
                        'id' => $this->game->getIdentifier(),
                        'filter' => 'date'),
                    array('force_canonical' => true)
                ).'?id='.$postTarget->getId().'&key='.$secretKey;
                // With core shortener helper
                $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

                $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);
                $this->getViewHelper('HeadMeta')->setProperty('twitter:card', "photo");
                $this->getViewHelper('HeadMeta')->setProperty('twitter:site', "@alfie_selfie");
                $this->getViewHelper('HeadMeta')->setProperty('twitter:title', $this->game->getTwShareMessage());
                $this->getViewHelper('HeadMeta')->setProperty('twitter:description', "");
                $this->getViewHelper('HeadMeta')->setProperty('twitter:image', $fbShareImage);
                $this->getViewHelper('HeadMeta')->setProperty('twitter:url', $socialLinkUrl);
            }
        }

        $viewModel->setVariables(array(
                'posts' => $paginator,
                'form' => $form,
                'statusMail' => $statusMail,
                'reportId' => $reportId,
                'filter' => $filter,
                'search' => $search,
            ));

        return $viewModel;
    }

    /**
     * If the user has already voted, we cancel her vote, else we create the vote.
     * TODO : we should distinguish between vote and like (so we should add the like field in post)
     */
    public function ajaxVoteAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $commentId = $this->getEvent()->getRouteMatch()->getParam('comment');
        $note = $this->getEvent()->getRouteMatch()->getParam('note');
        $request = $this->getRequest();
        $response = $this->getResponse();

        if (! $this->game) {
            $response->setContent(
                \Laminas\Json\Json::encode(
                    array(
                        'success' => 0
                    )
                )
            );

            return $response;
        }

        if (!$this->lmcUserAuthentication()->hasIdentity()) {
            $response->setContent(
                \Laminas\Json\Json::encode(
                    array(
                        'success' => 0
                    )
                )
            );
        } else {
            if ($request->isPost()) {
                $post = $this->getGameService()->getPostvotePostMapper()->findById($postId);
                $comment = ($commentId !== null) ? $this->getGameService()->getPostVoteCommentMapper()->findById($commentId) : null;
                if ($this->getGameService()->toggleVote(
                    $this->user,
                    $this->getRequest()->getServer('REMOTE_ADDR'),
                    $post,
                    $comment,
                    $note
                )) {
                    $response->setContent(
                        \Laminas\Json\Json::encode(['success' => 1])
                    );
                } else {
                    $response->setContent(
                        \Laminas\Json\Json::encode(['success' => 0])
                    );
                }
            }
        }

        return $response;
    }

    public function commentsAction()
    {
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        if ($postId) {
            $post = $this->getGameService()->getPostvotePostMapper()->findById($postId);
            $comments = $post->getComments();
        } else {
            $comments = $this->getGameService()->getCommentsForPostvote($this->game);
        }

        if (is_array($comments)) {
            $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($comments));
            $paginator->setItemCountPerPage(25);
            $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));
        } else {
            $paginator = $comments;
        }

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array('comments' => $paginator));

        return $viewModel;
    }

    public function commentAction()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            // Call this for the session lock to be released (other ajax calls can then be made)
            session_write_close();
        }
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $request = $this->getRequest();
        $response = $this->getResponse();
        $post = $this->getGameService()->getPostvotePostMapper()->findById($postId);

        if (!$this->lmcUserAuthentication()->hasIdentity()) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));
        } else {
            if ($request->isPost()) {
                if ($this->getGameService()->addComment(
                    $this->user,
                    $this->getRequest()->getServer('REMOTE_ADDR'),
                    $post,
                    $this->params()->fromPost('comment'),
                    $this->params()->fromPost('category')
                )) {
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'success' => 1
                    )));
                } else {
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'success' => 0
                    )));
                }
            }
        }

        //ajax call ?
        if ($this->getRequest()->isXmlHttpRequest()) {
            return $response;
        }

        $this->flashMessenger()->addMessage(
            $this->getServiceLocator()->get('MvcTranslator')->translate('Your comment has been recorded')
        );

        return $this->redirect()->toUrl($this->getRequest()->getServer('HTTP_REFERER'));
    }

    public function ajaxModerationAction()
    {
        $service = $this->getGameService();
        $postId = $this->getEvent()->getRouteMatch()->getParam('postId');
        $status = $this->getEvent()->getRouteMatch()->getParam('status');
        $response = $this->getResponse();

        $response->setContent(
            \Laminas\Json\Json::encode(
                array(
                    'success' => 0
                )
            )
        );

        if (!$postId) {
             $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));
        }
        $post = $service->getPostVotePostMapper()->findById($postId);

        if (! $post) {
             $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));
        }
        $game = $post->getPostvote();

        if ($status) {
            $service->moderatePost($post, $status);

            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 1
            )));
        }

        return $response;
    }

    public function ajaxRemoveCommentAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();
        $commentId = $this->getEvent()->getRouteMatch()->getParam('comment');
        $request = $this->getRequest();
        $response = $this->getResponse();

        if (! $this->game) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if (!$this->lmcUserAuthentication()->hasIdentity()) {
            $response->setContent(\Laminas\Json\Json::encode(array(
                'success' => 0
            )));
        } else {
            if ($request->isPost()) {
                if ($this->getGameService()->removeComment(
                    $this->user,
                    $this->getRequest()->getServer('REMOTE_ADDR'),
                    $commentId
                )) {
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'success' => 1
                    )));
                } else {
                    $response->setContent(\Laminas\Json\Json::encode(array(
                        'success' => 0
                    )));
                }
            }
        }

        return $response;
    }

    public function captchaAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', "image/png");

        $id = $this->params('id', false);

        if ($id) {
            $image = './data/captcha/' . $id;

            if (file_exists($image) !== false) {
                $imagegetcontent = file_get_contents($image);

                $response->setStatusCode(200);
                $response->setContent($imagegetcontent);

                if (file_exists($image) === true) {
                    unlink($image);
                }
            }
        }

        return $response;
    }

    public function unsharePostAction()
    {
        $response = $this->getResponse();
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $post = $this->getGameService()->getPostVotePostMapper()->findById($postId);

        if ($this->getRequest()->isPost()) {
            $this->getGameService()->removeShare($post, $this->user);
        }

        $response->setContent(
            \Laminas\Json\Json::encode(
                array(
                    'success' => 0
                )
            )
        );

        return $response;
    }

    public function sharePostAction()
    {
        $response = $this->getResponse();
        $statusMail = false;
        $message = '';

        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $post = $this->getGameService()->getPostVotePostMapper()->findById($postId);

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'postvote/post',
            array(
                'id' => $this->game->getIdentifier(),
                'post' => $post->getId(),
            ),
            array('force_canonical' => true)
        ).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $data['post'] = $post;
                $subject = $this->getGameService()->getOptions()->getSharePostSubjectLine();
                $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, null, 'share-post', $subject);
                if ($result) {
                    $statusMail = true;
                    //$this->getGameService()->addShare($post, $this->user, $this->getRequest()->getServer('REMOTE_ADDR'));
                    $this->getGameService()->addShare($post, $this->user);
                }
            } else {
                foreach ($form->getMessages() as $el => $errors) {
                    foreach ($errors as $key => $message) {
                        $message = $this->getServiceLocator()->get('MvcTranslator')->translate($message);
                    }
                }
            }
        }

        // $viewModel = $this->buildView($this->game);

        // $viewModel->setVariables(
        //     array(
        //         'statusMail'       => $statusMail,
        //         'message'          => $message,
        //         'form'             => $form,
        //         'socialLinkUrl'    => $socialLinkUrl,
        //         'post'             => $post
        //     )
        // );

        // return $viewModel;
        $response->setContent(
            \Laminas\Json\Json::encode(
                array(
                    'success' => 1
                )
            )
        );

        return $response;
    }

    public function shareCommentAction()
    {
        $statusMail = false;
        $message = '';

        $commentId = $this->getEvent()->getRouteMatch()->getParam('comment');
        $comment = $this->getGameService()->getPostVoteCommentMapper()->findById($commentId);

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $data['comment'] = $comment;
                $subject = $this->getGameService()->getOptions()->getShareCommentSubjectLine();
                $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, null, 'share-comment', $subject);
                if ($result) {
                    $statusMail = true;
                }
            } else {
                foreach ($form->getMessages() as $el => $errors) {
                    foreach ($errors as $key => $message) {
                        $message = $this->getServiceLocator()->get('MvcTranslator')->translate($message);
                    }
                }
            }
        }

        $viewModel = $this->buildView($this->game);

        $viewModel->setVariables(array(
            'statusMail' => $statusMail,
            'message'    => $message,
            'form'       => $form,
            'comment'    => $comment
        ));

        return $viewModel;
    }

    public function shareAction()
    {
        $statusMail = null;

        // Has the user finished the game ?
        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);

        if ($lastEntry === null) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'postvote',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $post = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntry));

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'postvote/post',
            array(
                'id' => $this->game->getIdentifier(),
                'post' => $post->getId(),
            ),
            array('force_canonical' => true)
        ).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        $viewModel = $this->buildView($this->game);

        foreach ($post->getPostElements() as $element) {
            $fbShareImage = $this->frontendUrl()->fromRoute(
                '',
                array(),
                array('force_canonical' => true),
                false
            ) . $element->getValue();
            break;
        }

        $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);
        $this->getViewHelper('HeadMeta')->setProperty('twitter:card', "photo");
        $this->getViewHelper('HeadMeta')->setProperty('twitter:site', "@playground");
        $this->getViewHelper('HeadMeta')->setProperty('twitter:title', $this->game->getTwShareMessage());
        $this->getViewHelper('HeadMeta')->setProperty('twitter:description', "");
        $this->getViewHelper('HeadMeta')->setProperty('twitter:image', $fbShareImage);
        $this->getViewHelper('HeadMeta')->setProperty('twitter:url', $socialLinkUrl);

        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
            'form'             => $form,
            'socialLinkUrl'    => $socialLinkUrl,
            'post'             => $post
        ));

        return $viewModel;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_postvote_service');
        }

        return $this->gameService;
    }
}
