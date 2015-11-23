<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Form\Form;

class PostVoteController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

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
        $sg         = $this->getGameService();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $game = $sg->checkGame($identifier);
        if (! $game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $game, $channel);
        if ($redirectFb) {
            return $redirectFb;
        }

        $user       = $this->zfcUserAuthentication()->getIdentity();

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute(''. $game->getClassType() . '/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $entry = $sg->play($game, $user);

        if (!$entry) {
            $lastEntry = $sg->findLastInactiveEntry($game, $user);
            if ($lastEntry === null) {
                return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }

            $lastEntryId = $lastEntry->getId();
            $lastPost = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntryId));
            $postId = $lastPost->getId();
            if ($lastPost->getStatus() == 2) {
                // the user has already taken part of this game and the participation limit has been reached
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('translator')->translate('You have already a Post'));

                return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote/post', array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            } else {
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('translator')->translate('Your Post is waiting for validation'));

                return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote/post', array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }
        }

        if (! $game->getForm()) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $form = $sg->createFormFromJson($game->getForm()->getForm(), 'postvoteForm');

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));
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
                } catch (\Zend\Form\Exception\InvalidElementException $e) {
                }
            }
        }

        $viewModel = $this->buildView($game);

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->getData();
                $post = $this->getGameService()->createPost($data, $game, $user, $form);

                if ($post && !empty($game->nextStep('play'))) {
                    // determine the route where the user should go
                    $redirectUrl = $this->frontendUrl()->fromRoute('postvote/'.$game->nextStep('play'), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

                    return $this->redirect()->toUrl($redirectUrl);
                }
            } else {
                $messages = $form->getMessages();
                $viewModel = $this->buildView($game);
                $viewModel->setVariables(array(
                    'success' => false,
                    'message' => implode(',', $messages['title']),
                ));
            }
        }

        $viewModel->setVariables(array(
                'playerData' => $entry->getPlayerData(),
                'form' => $form,
                'post' => $post,
            ));

        return $viewModel;
    }

    public function previewAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (! $game) {
            return $this->notFoundAction();
        }

        $entry = $sg->findLastActiveEntry($game, $user);
         
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote/'.$game->nextStep('preview'), array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));

        if (! $post) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        if ($this->getRequest()->isPost()) {
            $post = $this->getGameService()->confirmPost($game, $user);

            if ($post) {
                if (!($step = $game->nextStep('play'))) {
                    $step = 'result';
                }
                $redirectUrl = $this->frontendUrl()->fromRoute('postvote/'.$step, array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

                return $this->redirect()->toUrl($redirectUrl);
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array('post' => $post));

        return $viewModel;
    }

    /**
     * View the Post page
     * @return multitype:|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function postAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $postId = $this->getEvent()->getRouteMatch()->getParam('post');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $voted = false;

        $statusMail = false;
        $mailService = $this->getServiceLocator()->get('playgroundgame_message');
        $to = '';
        $skinUrl = $sg->getServiceManager()->get('ViewRenderer')->url('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true));
        $config = $this->getGameService()->getServiceManager()->get('config');
        if (isset($config['moderation']['email'])) {
            $to = $config['moderation']['email'];
        }

        $game = $sg->checkGame($identifier, false);

        if (! $postId) {
            return $this->notFoundAction();
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findById($postId);

        if (! $post || $post->getStatus() === 9) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $formModeration = new Form();
        $formModeration->setAttribute('method', 'post');

        $formModeration->add(array(
            'name' => 'moderation',
            'attributes' => array(
                'type' => 'hidden',
                'value' => '1'
            ),
        ));

        $form = new \PlaygroundGame\Form\Frontend\PostVoteVote($this->frontendUrl()->fromRoute('postvote/post/captcha', array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));

        if ($user) {
            $form->remove('captcha');
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
                    $result = $mailService->createHtmlMessage($from, $to, $subject, 'playground-game/email/moderation', array('data' => $data, 'skinUrl' => $skinUrl));
                    $mailService->send($result);
                    if ($result) {
                        $statusMail = true;
                        $reportId = $data['reportid'];
                    }
                }
            } else {
                $form->setData($request->getPost());
                if ($form->isValid()) {
                    if ($sg->addVote($user, $this->getRequest()->getServer('REMOTE_ADDR'), $post)) {
                        $voted = true;
                    } else {
                        $alreadyVoted = 'Vous avez déjà voté!';
                    }
                }
            }
        }

        $viewModel = $this->buildView($game);
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
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $statusMail = null;

        if (!$identifier) {
            return $this->notFoundAction();
        }

        $postVoteMapper = $this->getGameService()->getPostVoteMapper();
        $game = $postVoteMapper->findByIdentifier($identifier);

        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        // Has the user finished the game ?
        $lastEntry = $this->getGameService()->findLastInactiveEntry($game, $user);

        if ($lastEntry === null) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('postvote/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $this->getGameService()->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntry));

        if (! $post) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $viewModel = $this->buildView($game);

        $viewModel->setVariables(array(
                'statusMail'       => $statusMail,
                'post'             => $post,
                'form'             => $form,
            ));

        return $viewModel;
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
     * @return \Zend\Stdlib\ResponseInterface
     */
    public function ajaxuploadAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $request = $this->getRequest();
        $response = $this->getResponse();

        $game = $sg->checkGame($identifier);
        if (! $game) {
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        $entry = $sg->findLastActiveEntry($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if ($request->isPost()) {
            $data = $this->getRequest()->getFiles()->toArray();

            if (empty($data)) {
                $data = $this->getRequest()->getPost()->toArray();

                $key = key($data);

                $uploadImage = array('name' => $key.'.png', 'error' => 0, 'base64' => $data[$key]);
                $data = array($key => $uploadImage);
            }
            $uploadFile = $sg->uploadFileToPost($data, $game, $user);
        }

        $response->setContent(\Zend\Json\Json::encode(array(
                'success' => true,
                'fileUrl' => $uploadFile
            )));

        return $response;
    }

    public function ajaxdeleteAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $request = $this->getRequest();
        $response = $this->getResponse();

        $game = $sg->checkGame($identifier);
        if (! $game) {
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        $entry = $sg->findLastActiveEntry($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $sg->deleteFilePosted($data, $game, $user);
        }

        $response->setContent(\Zend\Json\Json::encode(array(
                'success' => true,
            )));

        return $response;
    }

    public function listAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $filter        = $this->getEvent()->getRouteMatch()->getParam('filter');
        $search    = $this->params()->fromQuery('name');
        $sg        = $this->getGameService();
        $postId     = $this->params()->fromQuery('id');

        $statusMail = false;
        $mailService = $this->getServiceLocator()->get('playgroundgame_message');
        $to = '';
        $skinUrl = $sg->getServiceManager()->get('ViewRenderer')->url('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true));
        $config = $this->getGameService()->getServiceManager()->get('config');
        if (isset($config['moderation']['email'])) {
            $to = $config['moderation']['email'];
        }

        $request = $this->getRequest();

        $game = $sg->checkGame($identifier, false);

        // Je recherche les posts validés associés à ce jeu
        $posts = $sg->findArrayOfValidatedPosts($game, $filter, $search);

        if (is_array($posts)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($posts));
            $paginator->setItemCountPerPage($game->getPostDisplayNumber());
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
                $result = $mailService->createHtmlMessage($from, $to, $subject, 'playground-game/email/moderation', array('data' => $data, 'skinUrl' => $skinUrl));
                $mailService->send($result);
                if ($result) {
                    $statusMail = true;
                    $reportId = $data['reportid'];
                }
            }
        }

        $viewModel = $this->buildView($game);
        
        if ($postId) {
            $postTarget = $sg->getPostVotePostMapper()->findById($postId);
            if ($postTarget) {
                foreach ($postTarget->getPostElements() as $element) {
                    $fbShareImage = $this->frontendUrl()->fromRoute('', array('channel' => ''), array('force_canonical' => true), false) . $element->getValue();
                    break;
                }
                
                $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
                
                // Without bit.ly shortener
                $socialLinkUrl = $this->frontendUrl()->fromRoute('postvote/list', array('id' => $game->getIdentifier(), 'filter' => 'date', 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?id='.$postTarget->getId().'&key='.$secretKey;
                // With core shortener helper
                $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);
                
                $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);
                
                $this->getViewHelper('HeadMeta')->setProperty('twitter:card', "photo");
                $this->getViewHelper('HeadMeta')->setProperty('twitter:site', "@alfie_selfie");
                $this->getViewHelper('HeadMeta')->setProperty('twitter:title', $game->getTwShareMessage());
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

    public function ajaxVoteAction()
    {
        // Call this for the session lock to be released (other ajax calls can then be made)
        session_write_close();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $postId     = $this->getEvent()->getRouteMatch()->getParam('post');
        $user        = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (! $game) {
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        $request = $this->getRequest();
        $response = $this->getResponse();

        if (!$this->zfcUserAuthentication()->hasIdentity()) {
            $response->setContent(\Zend\Json\Json::encode(array(
                    'success' => 0
            )));
        } else {
            if ($request->isPost()) {
                $post = $sg->getPostvotePostMapper()->findById($postId);
                if ($sg->addVote($user, $this->getRequest()->getServer('REMOTE_ADDR'), $post)) {
                    $response->setContent(\Zend\Json\Json::encode(array(
                        'success' => 1
                    )));
                } else {
                    $response->setContent(\Zend\Json\Json::encode(array(
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
    
    public function shareAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $statusMail = null;
    
        if (!$identifier) {
            return $this->notFoundAction();
        }
    
        $gameMapper = $this->getGameService()->getGameMapper();
        $game = $gameMapper->findByIdentifier($identifier);
    
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }
    
        // Has the user finished the game ?
        $lastEntry = $this->getGameService()->findLastInactiveEntry($game, $user);
    
        if ($lastEntry === null) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }
    
        $post = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntry));
    
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute('postvote/post', array('id' => $identifier, 'post' => $post->getId(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);
    
        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('postvote/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }
    
        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');
    
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        $viewModel = $this->buildView($game);
    
        foreach ($post->getPostElements() as $element) {
            $fbShareImage = $this->frontendUrl()->fromRoute('', array('channel' => ''), array('force_canonical' => true), false) . $element->getValue();
            break;
        }
    
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));

        // Without bit.ly shortener
        $socialLinkUrl = $this->frontendUrl()->fromRoute('postvote/list', array('id' => $game->getIdentifier(), 'filter' => 'date', 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?id='.$post->getId().'&key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);

        $this->getViewHelper('HeadMeta')->setProperty('twitter:card', "photo");
        $this->getViewHelper('HeadMeta')->setProperty('twitter:site', "@playground");
        $this->getViewHelper('HeadMeta')->setProperty('twitter:title', $game->getTwShareMessage());
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
