<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Lottery;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use PlaygroundGame\Service\GameService;

class GameController extends AbstractActionController
{

    /**
     * @var gameService
     */
    protected $gameService;

    protected $prizeService;

    protected $missionGameService;

    protected $options;

    public function homeAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');

        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // This fix exists only for safari in FB on Windows : we need to redirect the user to the page outside of iframe
        // for the cookie to be accepted. PlaygroundCore redirects to the FB Iframed page when
        // it discovers that the user arrives for the first time on the game in FB.
        // When core redirects, it adds a 'redir_fb_page_id' var in the querystring
        // Here, we test if this var exist, and then send the user back to the game in FB.
        // Now the cookie will be accepted by Safari...
        $pageId = $this->params()->fromQuery('redir_fb_page_id');
        if (!empty($pageId)) {
            $appId = 'app_'.$game->getFbAppId();
            $url = '//www.facebook.com/pages/game/'.$pageId.'?sk='.$appId;

            return $this->redirect()->toUrl($url);
        }

        return $this->forward()->dispatch('playgroundgame_'.$game->getClassType(), array('controller' => 'playgroundgame_'.$game->getClassType(), 'action' => $game->firstStep(), 'id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')));

    }

    public function indexAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        $isSubscribed = false;

         // Determine if the play button should be a CTA button (call to action)
        $isCtaActive = false;

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // If on Facebook, check if you have to be a FB fan to play the game

        if ($channel == 'facebook') {
            if ($game->getFbFan()) {
            	$isFan = $sg->checkIsFan($game);  
            	if (!$isFan) {
            		return $this->redirect()->toUrl($this->url()->fromRoute('frontend/' . $game->getClassType().'/fangate',array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            	}
            }

            $isCtaActive = true;
        }


        $subscription = $sg->checkExistingEntry($game, $user);
        if ($subscription) {
            $isSubscribed = true;
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game'             => $game,
            'isSubscribed'     => $isSubscribed,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'isCtaActive'      => $isCtaActive,
        ));

        return $viewModel;
    }

    /**
     * This action has been designed to be called by other controllers
     * It gives the ability to display an information form and persist it in the game entry
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function registerAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        $formPV = json_decode($game->getPlayerForm()->getForm());
        // TODO : create a Form class to implement this form
        $form = new Form();
        $form->setAttribute('id', 'playerForm');
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
            if (isset($element->line_email)) {
                $attributes  = $element->line_email[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $placeholder = isset($attributes->data->placeholder)? $attributes->data->placeholder : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';
                //$required    = ($attributes->data->required == 'true') ? true : false ;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';

                $element = new Element\Email($name);
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
            if (isset($element->line_radio)) {
                $attributes  = $element->line_radio[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';

//                 $required    = ($attributes->data->required == 'yes') ? true : false;
                $required = false;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
                $innerData   = isset($attributes->data->innerData)? $attributes->data->innerData : array();

                $element = new Element\Radio($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'name'          => $name,
                        'required' 		=> $required,
                        'allowEmpty'    => !$required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $values = array();
                foreach($innerData as $value){
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
                
                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
                
                $element->setOptions($options);
                
                $form->add($element);
                
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'allowEmpty' => !$required,
                )));
            }
            if (isset($element->line_checkbox)) {
                $attributes  = $element->line_checkbox[0];
                $name        = isset($attributes->name)? $attributes->name : '';
                $type        = isset($attributes->type)? $attributes->type : '';
                $position    = isset($attributes->order)? $attributes->order : '';
                $label       = isset($attributes->data->label)? $attributes->data->label : '';

//                 $required    = ($attributes->data->required == 'yes') ? true : false;
                $required = false;
                $class       = isset($attributes->data->class)? $attributes->data->class : '';
                $id          = isset($attributes->data->id)? $attributes->data->id : '';
                $lengthMin   = isset($attributes->data->length)? $attributes->data->length->min : '';
                $lengthMax   = isset($attributes->data->length)? $attributes->data->length->max : '';
                $innerData   = isset($attributes->data->innerData)? $attributes->data->innerData : array();

                $element = new Element\MultiCheckbox($name);
                $element->setLabel($label);
                $element->setAttributes(
                    array(
                        'name'     => $name,
                        'required' 		=> $required,
                        'allowEmpty'    => !$required,
                        'class' 		=> $class,
                        'id' 			=> $id
                    )
                );
                $values = array();
                foreach($innerData as $value){
                    $values[] = $value->label;
                }
                $element->setValueOptions($values);
                $form->add($element);

                $options = array();
                $options['encoding'] = 'UTF-8';
                $options['disable_inarray_validator'] = true;
                /*if ($lengthMin && $lengthMin > 0) {
                    $options['min'] = $lengthMin;
                }
                if ($lengthMax && $lengthMax > $lengthMin) {
                    $options['max'] = $lengthMax;
                    $element->setAttribute('maxlength', $lengthMax);
                    $options['messages'] = array(\Zend\Validator\StringLength::TOO_LONG => sprintf($this->getServiceLocator()->get('translator')->translate('This field contains more than %s characters', 'playgroundgame'), $lengthMax));
                }*/
                
                $element->setOptions($options);
                
                $inputFilter->add($factory->createInput(array(
                    'name'     => $name,
                    'required' => $required,
                    'allowEmpty' => !$required,
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

        if ($this->getRequest()->isPost()) {
            // POST Request: Process form
            $data = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            
            $form->setData($data);

            if ($form->isValid()) {
                $data = json_encode($form->getData());
                
                $steps = $game->getStepsArray();
                $key = array_search($this->params('action'), $steps);
                $keyplay = array_search('play', $steps);
                
                // If register step before play, I don't have no entry yet. I have to create one
                // If register after play step, I search for the last entry created by play step.
                if($key && $key < $keyplay){
                    $entry = $sg->play($game, $user);
                    if (!$entry) {
                        // the user has already taken part of this game and the participation limit has been reache
                        $this->flashMessenger()->addMessage('Vous avez déjà participé');
                    
                        return $this->redirect()->toUrl($this->url()->fromRoute('frontend/'.$game->getClassType().'/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
                    }
                }else{
                    $entry = $sg->findLastEntry($game, $user);
                }
                
                $entry->setPlayerData($data);
                $sg->getEntryMapper()->update($entry);

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/'. $game->getClassType() .'/' . $game->nextStep($this->params('action')), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'form' => $form,
            'title' => $game->getPlayerForm()->getTitle(),
            'description' => $game->getPlayerForm()->getDescription(),
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

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

    public function fbrequestAction()
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

        $sg->postFbRequest($fbId, $game, $user);

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

    public function termsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    public function conditionsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    public function bounceAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $availableGames = $sg->getAvailableGames($user);

        $rssUrl = '';
        $config = $sg->getServiceManager()->get('config');
        if (isset($config['rss']['url'])) {
            $rssUrl = $config['rss']['url'];
        }

		$viewModel = $this->buildView($game);
		$viewModel->setVariables(array(
		    'rssUrl'         => $rssUrl,
            'game'           => $game,
            'user'           => $user,
            'availableGames' => $availableGames,
            'flashMessages'  => $this->flashMessenger()->getMessages(),
		));

        return $viewModel;
    }

    /**
     * Send mail for winner and/or looser
     * @param unknown $game
     * @param unknown $user
     * @param unknown $lastEntry
     */
    public function sendMail($game, $user, $lastEntry, $prize = NULL){
        if($user && $game->getMailWinner() && $lastEntry->getWinner()){
            $this->getGameService()->sendResultMail($game, $user, $lastEntry, 'winner', $prize);
        }

        if($user && $game->getMailLooser() && !$lastEntry->getWinner()){
            $this->getGameService()->sendResultMail($game, $user, $lastEntry, 'looser');
        }
    }

    public function checkFbRegistration($user, $game, $channel)
    {
        $redirect = false;
        $session = new Container('facebook');
        $sg = $this->getGameService();
        if ($channel == 'facebook' && $session->offsetExists('signed_request')) {
            if (!$user) {
                // Get Playground user from Facebook info
                $beforeLayout = $this->layout()->getTemplate();
                $view = $this->forward()->dispatch('playgrounduser_user', array('controller' => 'playgrounduser_user', 'action' => 'registerFacebookUser', 'provider' => $channel));

                $this->layout()->setTemplate($beforeLayout);
                $user = $view->user;

                // If the user can not be created/retrieved from Facebook info, redirect to login/register form
                if (!$user){
                    $redirectUrl = urlencode($this->url()->fromRoute('frontend/'. $game->getClassType() .'/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    $redirect =  $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirectUrl);
                }

            }

            if($game->getFbFan()){
                if ($sg->checkIsFan($game) === false){
                    $redirect =  $this->redirect()->toRoute($game->getClassType().'/fangate',array('id' => $game->getIdentifier()));
                }
            }
        }

        return $redirect;
    }

    public function buildView($game)
    {
        $viewModel = new ViewModel();

        $this->addMetaTitle($game);
        $this->addMetaBitly();
        $this->addGaEvent($game);

        $this->customizeGameDesign($game);
        $this->addColRight($game);
        $this->addColLeft($game);
        
        // this is possible to create a specific game design in /design/frontend/default/custom. It will precede all others templates.
        $templatePathResolver = $this->getServiceLocator()->get('Zend\View\Resolver\TemplatePathStack');
        $l = $templatePathResolver->getPaths();
        
        // TODO : Improve : I take the last path to add the game id without verifying it's correct
        $templatePathResolver->addPath($l[0].'custom/'.$game->getIdentifier());
        
        $view = $this->addAdditionalView($game);
        if ($view and $view instanceof \Zend\View\Model\ViewModel) {
            $viewModel->addChild($view, 'additional');
        } elseif ($view and $view instanceof \Zend\Http\PhpEnvironment\Response) {
            return $view;
        }

        $viewModel->setVariables($this->getShareData($game));

        return $viewModel;
    }

    public function addAdditionalView($game)
    {
        $view = false;
        $actionName = $this->getEvent()->getRouteMatch()->getParam('action', 'not-found');
        //TODO : improve the way steps and steps views are managed
        $stepsViews = json_decode($game->getStepsViews(), true);
        if($stepsViews && isset($stepsViews[$actionName]) && is_string($stepsViews[$actionName])){
            $action = $stepsViews[$actionName];
            $beforeLayout = $this->layout()->getTemplate();
            $view = $this->forward()->dispatch('playgroundgame_game', array('action' => $action, 'id' => $game->getIdentifier()));
            // TODO : suite au forward, le template de layout a changé, je dois le rétablir...
            $this->layout()->setTemplate($beforeLayout);

        }

        return $view;
    }

    public function addColRight($game)
    {
        $colRight = new ViewModel();
        $colRight->setTemplate($this->layout()->col_right);
        $colRight->setVariables(array('game' => $game));

        $this->layout()->addChild($colRight, 'column_right');
    }

    public function addColLeft($game)
    {
        $colLeft = new ViewModel();
        $colLeft->setTemplate($this->layout()->col_right);
        $colLeft->setVariables(array('game' => $game));

        $this->layout()->addChild($colLeft, 'column_left');
    }

    public function addMetaBitly()
    {
        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);
    }

    public function addGaEvent($game)
    {
        // Google Analytics event
        $ga = $this->getServiceLocator()->get('google-analytics');
        $event = new \PlaygroundCore\Analytics\Event($game->getClassType(), $this->params('action'));
        $event->setLabel($game->getTitle());
        $ga->addEvent($event);
    }

    public function addMetaTitle($game)
    {
        $title = $game->getTitle();
        // Meta set in the layout
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $title,
                'currentPage' => array(
                    'pageGames' => 'games',
                    'pageWinners' => ''
                ),
                'headParams' => array(
                    'headTitle' => $title,
                    'headDescription' => $title,
                ),
                'bodyCss' => $game->getIdentifier()
            )
        );
    }

    public function customizeGameDesign($game)
    {
        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
        }
    }

    public function getShareData($game)
    {
        // I change the fbappid if i'm in fb
        if($this->getEvent()->getRouteMatch()->getParam('channel') === 'facebook'){
            $fo = $this->getServiceLocator()->get('facebook-opengraph');
            $fo->setId($game->getFbAppId());
        }

        // If I want to add a share block in my view
        if ($game->getFbShareMessage()) {
            $fbShareMessage = $game->getFbShareMessage();
        } else {
            $fbShareMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage());
        }

        if ($game->getFbShareImage()) {
            $fbShareImage = $this->url()->fromRoute('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)) . $game->getFbShareImage();
        } else {
            $fbShareImage = $this->url()->fromRoute('frontend', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)) . $game->getMainImage();
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));

        // Without bit.ly shortener
        $socialLinkUrl = $this->url()->fromRoute('frontend/' . $game->getClassType(), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true));
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        // FB Requests only work when it's a FB app
        if ($game->getFbRequestMessage()) {
            $fbRequestMessage = urlencode($game->getFbRequestMessage());
        } else {
            $fbRequestMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage());
        }

        if ($game->getTwShareMessage()) {
            $twShareMessage = $game->getTwShareMessage() . $socialLinkUrl;
        } else {
            $twShareMessage = str_replace('__placeholder__', $game->getTitle(), $this->getOptions()->getDefaultShareMessage()) . $socialLinkUrl;
        }

        $this->getViewHelper('HeadMeta')->setProperty('og:title', $fbShareMessage);
        $this->getViewHelper('HeadMeta')->setProperty('og:image', $fbShareImage);

        $data = array(
            'socialLinkUrl'       => $socialLinkUrl,
            'secretKey'           => $secretKey,
            'fbShareMessage'      => $fbShareMessage,
            'fbShareImage'        => $fbShareImage,
            'fbRequestMessage'    => $fbRequestMessage,
            'twShareMessage'      => $twShareMessage,
        );

        return $data;
    }

    public function prizesAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$user = $this->zfcUserAuthentication()->getIdentity();
    	$sg = $this->getGameService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

    	if (count($game->getPrizes()) == 0){
    		return $this->notFoundAction();
    	}

    	// If this game has a specific layout...
    	if ($game->getLayout()) {
    		$layoutViewModel = $this->layout();
    		$layoutViewModel->setTemplate($game->getLayout());
    	}

    	// If this game has a specific stylesheet...
    	if ($game->getStylesheet()) {
    		$this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
    	}

    	/*$adserving = $this->getOptions()->getAdServing();
    	$adserving['cat2'] = 'game';
    	$adserving['cat3'] = '&EASTgameid='.$game->getId();*/
    	// I change the label in the breadcrumb ...
    	$this->layout()->setVariables(
			array(
				'breadcrumbTitle' => $game->getTitle(),
				//'adserving'       => $adserving,
				'currentPage' => array(
						'pageGames' => 'games',
						'pageWinners' => ''
				),
                'headParams' => array(
                    'headTitle' => $game->getTitle(),
                    'headDescription' => $game->getTitle(),
                ),
			)
    	);

    	$bitlyclient = $this->getOptions()->getBitlyUrl();
    	$bitlyuser = $this->getOptions()->getBitlyUsername();
    	$bitlykey = $this->getOptions()->getBitlyApiKey();

    	$this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

    	// right column
    	$column = new ViewModel();
    	$column->setTemplate($this->layout()->col_right);
    	$column->setVariables(array('game' => $game));

    	$this->layout()->addChild($column, 'column_right');

    	$viewModel = new ViewModel(
    		array(
    			'game'             => $game,
    			'flashMessages'    => $this->flashMessenger()->getMessages(),
    		)
    	);

    	return $viewModel;
    }

    public function prizeAction()
    {
    	$identifier = $this->getEvent()->getRouteMatch()->getParam('id');
    	$user = $this->zfcUserAuthentication()->getIdentity();
    	$sg = $this->getGameService();

    	$game = $sg->checkGame($identifier);
    	if (!$game) {
    		return $this->notFoundAction();
    	}

    	$prizeIdentifier = $this->getEvent()->getRouteMatch()->getParam('prize');
		$sp = $this->getPrizeService();

		$prize = $sp->getPrizeMapper()->findByIdentifier($prizeIdentifier);
		if (!$prize) {
			return $this->notFoundAction();
		}

    	// If this game has a specific layout...
    	if ($game->getLayout()) {
    		$layoutViewModel = $this->layout();
    		$layoutViewModel->setTemplate($game->getLayout());
    	}

    	// If this game has a specific stylesheet...
    	if ($game->getStylesheet()) {
    		$this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
    	}

    	// I change the label in the breadcrumb ...
    	$this->layout()->setVariables(
    			array(
    					'breadcrumbTitle' => $game->getTitle(),
    					'currentPage' => array(
    							'pageGames' => 'games',
    							'pageWinners' => ''
    					),
                        'headParams' => array(
                            'headTitle' => $game->getTitle(),
                            'headDescription' => $game->getTitle(),
                        ),
    			)
    	);

    	$bitlyclient = $this->getOptions()->getBitlyUrl();
    	$bitlyuser = $this->getOptions()->getBitlyUsername();
    	$bitlykey = $this->getOptions()->getBitlyApiKey();

    	$this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
    	$this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

    	// right column
    	$column = new ViewModel();
    	$column->setTemplate($this->layout()->col_right);
    	$column->setVariables(array('game' => $game));

    	$this->layout()->addChild($column, 'column_right');

    	$viewModel = new ViewModel(
    		array(
    			'game'             => $game,
    			'prize'     	   => $prize,
  				'flashMessages'    => $this->flashMessenger()->getMessages(),
   			)
    	);

    	return $viewModel;
    }

    public function gameslistAction()
    {

        $layoutViewModel = $this->layout();
        $layoutViewModel->setTemplate('layout/gameslist-2columns-right.phtml');

        $slider = new ViewModel();
        $slider->setTemplate('playground-game/common/top_promo');

        $sliderItems = $this->getGameService()->getActiveSliderGames();

        $slider->setVariables(array('sliderItems' => $sliderItems));

        $layoutViewModel->addChild($slider, 'slider');

        $games = $this->getGameService()->getActiveGames(false,'','endDate');
        if (is_array($games)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($games));
        } else {
            $paginator = $games;
        }

        $paginator->setItemCountPerPage(7);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        $bitlyclient = $this->getOptions()->getBitlyUrl();
        $bitlyuser = $this->getOptions()->getBitlyUsername();
        $bitlykey = $this->getOptions()->getBitlyApiKey();

        $this->getViewHelper('HeadMeta')->setProperty('bt:client', $bitlyclient);
        $this->getViewHelper('HeadMeta')->setProperty('bt:user', $bitlyuser);
        $this->getViewHelper('HeadMeta')->setProperty('bt:key', $bitlykey);

        $this->layout()->setVariables(
           array(
            'sliderItems'   => $sliderItems,
            /*'adserving'       => array(
                'cat1' => 'playground',
                'cat2' => 'game',
                'cat3' => ''
            ),*/
            'currentPage' => array(
                'pageGames' => 'games',
                'pageWinners' => ''
            ),
           )
        );

        return new ViewModel(
            array(
                'games'       => $paginator
            )
        );
    }

    public function fangateAction()
    {

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $sg = $this->getGameService();
        $game = $sg->checkGame($identifier, false);
        if (!$game) {
            return $this->notFoundAction();
        }

        // If this game has a specific layout...
        if ($game->getLayout()) {
            $layoutViewModel = $this->layout();
            $layoutViewModel->setTemplate($game->getLayout());
        }

        // If this game has a specific stylesheet...
        if ($game->getStylesheet()) {
            $this->getViewHelper('HeadLink')->appendStylesheet($this->getRequest()->getBaseUrl(). '/' . $game->getStylesheet());
        }

        // I change the label in the breadcrumb ...
        $this->layout()->setVariables(
            array(
                'breadcrumbTitle' => $game->getTitle(),
                'headParams' => array(
                    'headTitle' => $game->getTitle(),
                    'headDescription' => $game->getTitle(),
                ),
            )
        );

        $viewModel = new ViewModel(
            array(
                'game'             => $game,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
            )
        );

        return $viewModel;
    }

    protected function getViewHelper($helperName)
    {
        return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_lottery_service');
        }

        return $this->gameService;
    }

    public function getMissionGameService()
    {
        if (!$this->missionGameService) {
            $this->missionGameService = $this->getServiceLocator()->get('playgroundgame_mission_game_service');
        }

        return $this->missionGameService;
    }

    public function setMissionGameService(GameService $missionGameService)
    {
        $this->missionGameService = $missionGameService;

        return $this;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }

    public function getPrizeService()
    {
    	if (!$this->prizeService) {
    		$this->prizeService = $this->getServiceLocator()->get('playgroundgame_prize_service');
    	}

    	return $this->prizeService;
    }

    public function setPrizeService(PrizeService $prizeService)
    {
    	$this->prizeService = $prizeService;

    	return $this;
    }

    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions($this->getServiceLocator()->get('playgroundcore_module_options'));
        }

        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}