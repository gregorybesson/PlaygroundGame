<?php

namespace PlaygroundGame\Controller\Frontend;

use PlaygroundGame\Entity\Entry;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Form\Form;

class InstantWinController extends GameController
{

    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {

        $sg = $this->getGameService();
        $occurrence_mapper = $sg->getInstantWinOccurrenceMapper();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $game, $channel);
        if($redirectFb){
            return $redirectFb;
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->url()->fromRoute('frontend/'. $game->getClassType() . '/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        if ($game->getOccurrenceType()=='datetime') {

            /*
            $beforeLayout = $this->layout()->getTemplate();
            // je délègue la responsabilité du formulaire à PlaygroundUser, y compris dans sa gestion des erreurs
            $form_address = $this->forward()->dispatch('playgrounduser_user', array('action' => 'address'));
            // TODO : suite au forward, le template de layout a changé, je dois le rétablir...
            $this->layout()->setTemplate($beforeLayout);
            // Le formulaire est validé, il renvoie true et non un ViewModel


            if (!($form_address instanceof \Zend\View\Model\ViewModel)) {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            }
            */
            if ($this->getRequest()->isPost()) {
                // En post, je reçois la maj du form pour les gagnants. Je n'ai pas à créer une nouvelle participation mais vérifier la précédente
                $lastEntry = $sg->findLastInactiveEntry($game, $user);
                if (!$lastEntry) {
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }
                $winner = $lastEntry->getWinner();
                // if not winner, I'm not authorized to call this page in POST mode.
                if (!$winner) {
                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }

                // si la requete est POST et que j'arrive ici, c'est que le formulaire contient une erreur. Donc je prépare la vue formulaire sans le grattage
                //$viewModel->setTemplate('instant-win/winner/form');
            } else {
                // J'arrive sur le jeu, j'essaie donc de participer
                $entry = $sg->play($game, $user);
                if (!$entry) {
                    // the user has already taken part of this game and the participation limit has been reached
                    $this->flashMessenger()->addMessage('Vous avez déjà participé');

                    return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result',array('id' => $game->getIdentifier(), 'channel' => $channel)));
                }

                // update the winner attribute in entry.
                $winner = $sg->IsInstantWinner($game, $user);
            }
            $prize = null;
            if ($winner){
                $prize = $winner->getPrize();
            }
            $viewVariables = array(
                'game' => $game,
                'winner' => $winner,
                'flashMessages' => $this->flashMessenger()->getMessages(),
                'prize' => $prize,
                'over' => false,
            );
            //$viewModel->addChild($form_address, 'form_address');
        } elseif ($game->getOccurrenceType()=='code') {
            $form = $this->getServiceLocator()->get('playgroundgame_instantwinoccurrencecode_form');
            $form->setAttribute('action', $this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            $occurrence = null;
            if ($this->getRequest()->isPost()){
                $form->setData($this->getRequest()->getPost());
                if ($form->isValid()) {
                    $data =  $form->getData('code-input');
                    $code = filter_var($data['code-input'], FILTER_SANITIZE_STRING);
                    $occurrence = $this->getGameService()->isInstantWinner($game,$user,$code);
                    if (!$occurrence) {
                        $this->flashMessenger()->addMessage('Le code entré est invalide ou a déjà été utilisé !');
                         return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    } else {
                        return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
                    }
                }
            }
            $viewVariables = array(
                'game' => $game,
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            );
        }
        $viewModel = $this->buildView($game);
        if ($viewModel instanceof \Zend\View\Model\ViewModel) {
            $viewModel->setVariables($viewVariables);
        }

        return $viewModel;
    }

    public function resultAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');
        $user   = $this->zfcUserAuthentication()->getIdentity();
        $sg     = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $lastEntry = $sg->findLastInactiveEntry($game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
        }
        $winner = $lastEntry->getWinner();
        $occurrence = null;

        // On tente de récupèrer l'occurrence si elle existe pour avoir accés au lot associé
        $occurrences = $sg->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game->getId(), 'entry' => $lastEntry->getId(), ));
        if (!empty($occurrences)) {
            $occurrence = current($occurrences);
        }

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->url()->fromRoute('frontend/instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));
        $socialLinkUrl = $this->url()->fromRoute('frontend/instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        $statusMail = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                if(isset($data['email1']) || isset($data['email2']) || isset($data['email3'])) {
                    $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                    if ($result) {
                        $statusMail = true;
                    }
                }
            }
        }

        $prize = NULL;
        if ($occurrence instanceof \PlaygroundGame\Entity\InstantWinOccurrence){
            $prize = $occurrence->getPrize();
        }
        $this->sendMail($game, $user, $lastEntry, $prize);

        $nextGame = parent::getMissionGameService()->checkCondition($game, $winner, true, $lastEntry);

        $viewModel = $this->buildView($game);
        if ($viewModel instanceof \Zend\View\Model\ViewModel) {
            $viewModel->setVariables(array(
                'occurrence'       => $occurrence,
                'statusMail'       => $statusMail,
                'game'             => $game,
                'winner'           => $winner,
                'flashMessages'    => $this->flashMessenger()->getMessages(),
                'form'             => $form,
                'socialLinkUrl'    => $socialLinkUrl,
                'secretKey'        => $secretKey,
                'nextGame'         => $nextGame,
            ));
        }
        return $viewModel;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_instantwin_service');
        }

        return $this->gameService;
    }
}
