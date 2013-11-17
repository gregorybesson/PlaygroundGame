<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Session\Container;
use PlaygroundGame\Form\Frontend\PostVoteVote;
use Zend\View\Model\ViewModel;


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
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (! $game) {
            return $this->notFoundAction();
        }

        $session = new Container('facebook');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        // Redirect to fan gate if the game require to 'like' the page before playing

        if ($channel == 'facebook' && $session->offsetExists('signed_request')) {
            if($game->getFbFan()){
                if ($sg->checkIsFan($game) === false){
                    return $this->redirect()->toRoute($game->getClassType().'/fangate',array('id' => $game->getIdentifier()));
                }
            }
        }

        if (!$user) {

            // The game is deployed on Facebook, and played from Facebook : retrieve/register user

            if ($channel == 'facebook' && $session->offsetExists('signed_request')) {

                // Get Playground user from Facebook info

                $viewModel = $this->buildView($game);
                $beforeLayout = $this->layout()->getTemplate();

                $view = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user','action' => 'registerFacebookUser', 'provider' => $channel));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirect = urlencode($this->url()->fromRoute('frontend/postvote/play', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
                }

                // The game is not played from Facebook : redirect to login/register form

            } else {
                $redirect = urlencode($this->url()->fromRoute('frontend/postvote/play', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
            }

        }

        $entry = $sg->play($game, $user);

        if (!$entry) {
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if ($lastEntry == null) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }

            $lastEntryId = $lastEntry->getId();
            $lastPost = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $lastEntryId));
            $postId = $lastPost->getId();
            if ($lastPost->getStatus() == 2) {
                // the user has already taken part of this game and the participation limit has been reached
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('translator')->translate('You have already a Post'));

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote/post',array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            } else {
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('translator')->translate('Your Post is waiting for validation'));

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote/post', array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }
        }

        // TODO : Don't display incomplete post & vote without form...
        if (! $game->getForm()) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $formPV = json_decode($game->getForm()->getForm());
        // TODO : create a Form class to implement this form
        $form = new Form();
        $form->setAttribute('id', 'postForm');
        $form->setAttribute('enctype', 'multipart/form-data');

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();

        foreach ($formPV as $element) {
            if (isset($element->line_text)) {
                $attributes  = $element->line_text[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';

                $element = new Element\Text($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'placeholder' 	=> $placeholder,
                        'required' 		=> $required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $form->add($element);

                $options = array();
                $options['encoding'] = 'UTF-8';
                if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                    $options['messages'] = array(\Zend\Validator\StringLength::TOO_LONG => sprintf($this->getServiceLocator()->get('translator')->translate('This field contains more than %s characters', 'playgroundgame'), $lengthMax));
                }
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => $options,
                        ),
                    ),
                )));

            }
            if (isset($element->line_paragraph)) {
                $attributes  = $element->line_paragraph[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';

                $element = new Element\Textarea($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'placeholder' 	=> $placeholder,
                        'required' 		=> $required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $form->add($element);

                $options = array();
                $options['encoding'] = 'UTF-8';
                if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                }
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => $options,
                        ),
                    ),
                )));
            }
            if (isset($element->line_upload)) {
                $attributes  = $element->line_upload[0];
                //print_r($attributes);
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                $required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $filesizeMin = isset($attributes->data->filesize)? $attributes->data->filesize->min : '';
                $filesizeMax = isset($attributes->data->filesize)? $attributes->data->filesize->max : '';
                $element = new Element\File($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'required' 	=> $required,
                        'class' 	=> $class,
                        'id' 		=> $id
                    )
                );
                $form->add($element);

                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'validators' => array(
                            array('name' => '\Zend\Validator\File\Size', 'options' => array('max' => 10*1024*1024)),
                            array('name' => '\Zend\Validator\File\Extension', 'options'  => array('png,PNG,jpg,JPG,jpeg,JPEG,gif,GIF', 'messages' => array(
                            \Zend\Validator\File\Extension::FALSE_EXTENSION => 'Veuillez télécharger une image' ))
                        ),
                    ),
                )));

            }
        }

		$form->setInputFilter($inputFilter);

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));
        if ($post) {
            foreach ($post->getPostElements() as $element) {
                if ($form->get($element->getName())) {
                    $form->get($element->getName())->setValue($element->getValue());

					$elementType = $form->get($element->getName())->getAttribute('type');
					if($elementType == 'file' && $element->getValue() != ''){
						$filter = $form->getInputFilter();
        				$elementInput = $filter->get($element->getName());
						$elementInput->setRequired(false);
						$form->get($element->getName())->setAttribute('required', false);
					}
                }
            }
        }

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

                if ($post) {
                    // determine the route where the user should go
                    $redirectUrl = $this->url()->fromRoute('frontend/postvote/preview', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

                    return $this->redirect()->toUrl($redirectUrl);
                }
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
                'game' => $game,
                'form' => $form,
                'post' => $post,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );

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

        $entry = $sg->getEntryMapper()->findLastActiveEntryById($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote/bounce',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findOneBy(array('entry' => $entry, 'status' => 0));

        if (! $post) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        if ($this->getRequest()->isPost()) {

            $post = $this->getGameService()->confirmPost($game, $user);

            if ($post) {
		        // send mail for participation
		        $this->getGameService()->sendGameMail($game, $user, $post, 'postvote');
                $redirectUrl = $this->url()->fromRoute('frontend/postvote/result', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

                return $this->redirect()->toUrl($redirectUrl);
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
                'game' => $game,
                'post' => $post,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            )
        );

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
        /*if (! $game) {
            return $this->notFoundAction();
        }*/

        if (! $postId) {
            return $this->notFoundAction();
        }

        // Je recherche le post associé à entry + status == 0. Si non trouvé, je redirige vers home du jeu.
        $post = $sg->getPostVotePostMapper()->findById($postId);

        if (! $post) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
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

        $form = new \PlaygroundGame\Form\Frontend\PostVoteVote($this->url()->fromRoute('frontend/postvote/post/captcha',array('id' => $identifier, 'post' => $postId, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));

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
                'game'  => $game,
                'post'  => $post,
                'voted' => $voted,
                'form'  => $form,
                'formModeration' => $formModeration,
                'statusMail' => $statusMail,
                'flashMessages' => $this->flashMessenger()->getMessages(),
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
        $sg = $this->getGameService();

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
        $lastEntry = $this->getGameService()->getEntryMapper()->findLastInactiveEntryById($game, $user);

        if ($lastEntry == null) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/postvote', array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user);
                if ($result) {
                    $statusMail = true;
                }
            }
        }

        $nextGame = parent::getMissionGameService()->checkCondition($game, $lastEntry->getWinner(), true, $lastEntry);

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
                'statusMail'       => $statusMail,
                'game'             => $game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
                'form'             => $form,
                'nextGame'         => $nextGame,
            )
        );

        return $viewModel;
    }

    public function fbshareAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$fbId) {
            return false;
        }

        $sg->postFbWall($fbId, $game, $user);

        return true;

    }

    public function tweetAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $tweetId = $this->params()->fromQuery('tweetId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$tweetId) {
            return false;
        }

        $sg->postTwitter($tweetId, $game, $user);

        return true;

    }

    public function googleAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $googleId = $this->params()->fromQuery('googleId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game) {
            return false;
        }
        $subscription = $sg->checkExistingEntry($game, $user);
        if (! $subscription) {
            return false;
        }
        if (!$googleId) {
            return false;
        }

        $sg->postGoogle($googleId, $game, $user);

        return true;

    }

    /**
     * Example of AJAX File Upload with Session Progress and partial validation.
     *
     * @return array|ViewModel
     */
    public function ajaxuploadAction()
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

        $entry = $sg->getEntryMapper()->findLastActiveEntryById($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if ($request->isPost()) {
            $data = $this->getRequest()->getFiles()->toArray();
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

        $entry = $sg->getEntryMapper()->findLastActiveEntryById($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $response->setContent(\Zend\Json\Json::encode(array(
                'success' => 0
            )));

            return $response;
        }

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $deleteFile = $sg->deleteFilePosted($data, $game, $user);
        }

        $response->setContent(\Zend\Json\Json::encode(array(
                'success' => true,
            )));

        return $response;
    }

    public function listAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
		$filter		= $this->getEvent()->getRouteMatch()->getParam('filter');
        $search 	= $this->params()->fromQuery('name');
        $user 		= $this->zfcUserAuthentication()->getIdentity();
        $sg 		= $this->getGameService();

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
        /*if (! $game) {
            return $this->notFoundAction();
        }*/

        // Je recherche les posts validés associés à ce jeu
        $posts = $sg->findArrayOfValidatedPosts($game, $filter, $search);

        if (is_array($posts)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($posts));
            $paginator->setItemCountPerPage(7);
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
        $viewModel->setVariables(array(
                'game' => $game,
                'posts' => $paginator,
                'form' => $form,
                'statusMail' => $statusMail,
                'reportId' => $reportId,
                'filter' => $filter,
                'search' => $search,
            )
        );

        return $viewModel;
    }

    public function ajaxVoteAction ()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $postId     = $this->getEvent()->getRouteMatch()->getParam('post');
        $user 		= $this->zfcUserAuthentication()->getIdentity();
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
                $imagegetcontent = @file_get_contents($image);

                $response->setStatusCode(200);
                $response->setContent($imagegetcontent);

                if (file_exists($image) == true) {
                    unlink($image);
                }
            }

        }

        return $response;
    }

    protected function getViewHelper($helperName)
    {
        return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_postvote_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }
}
