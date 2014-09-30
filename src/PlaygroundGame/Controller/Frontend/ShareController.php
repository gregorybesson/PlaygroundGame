<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Form\Element;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\Session\Container;
use PlaygroundGame\Form\Frontend\PostVoteVote;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;


class ShareController extends GameController
{
    
    public function indexAction()
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
        
        if ($lastEntry == null) {
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
        
        // buildView must be before sendMail because it adds the game template path to the templateStack
        // TODO : Improve this.
        $viewModel = $this->buildView($game);
        
        $this->sendMail($game, $user, $lastEntry);
        
        $nextGame = parent::getMissionGameService()->checkCondition($game, $lastEntry->getWinner(), true, $lastEntry);
        
        $viewModel->setVariables(array(
            'statusMail'       => $statusMail,
            'game'             => $game,
            'flashMessages'    => $this->flashMessenger()->getMessages(),
            'form'             => $form,
            'nextGame'         => $nextGame,
        ));
        
        return $viewModel;
    }
    
    public function fbshareAction()
    {
        $viewModel = new JsonModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $viewModel->setVariable('', $value);
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->error();
        }
        if (!$fbId) {
            return $this->error();
        }
    
        $sg->postFbWall($fbId, $game, $user, $entry);
    
        return $this->success();
    
    }
    
    public function fbrequestAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $fbId = $this->params()->fromQuery('fbId');
        $to = $this->params()->fromQuery('to');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $this->error();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->error();
        }
        if (!$fbId) {
            return $this->error();
        }
    
        $sg->postFbRequest($fbId, $game, $user, $entry, $to);
    
        return $this->success();
    
    }
    
    public function tweetAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $tweetId = $this->params()->fromQuery('tweetId');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();
    
        $game = $sg->checkGame($identifier);
        if (!$game) {
            return $this->error();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->error();
        }
        if (!$tweetId) {
            return $this->error();
        }
    
        $sg->postTwitter($tweetId, $game, $user, $entry);
    
        return $this->success();
    
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
            return $this->error();
        }
        $entry = $sg->checkExistingEntry($game, $user);
        if (! $entry) {
            return $this->error();
        }
        if (!$googleId) {
            return $this->error();
        }
    
        $sg->postGoogle($googleId, $game, $user, $entry);
    
        return $this->success();
    
    }
    
    /**
     * 
     * @param array $data
     * @return \Zend\View\Model\JsonModel
     */
    protected function success($data = null)
    {
        $model = new JsonModel(array(
            'success' => true,
            'data' => $data
        ));
        return $model->setTerminal(true);
    }
    
    /**
     *
     * @param string $message
     * @return \Zend\View\Model\JsonModel
     */
    protected function error($message = null)
    {
        $model = new JsonModel(array(
            'success' => true,
            'message' => $data
        ));
        return $model->setTerminal(true);
    }
    
}
