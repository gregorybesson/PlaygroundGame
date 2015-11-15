<?php

namespace PlaygroundGame\Controller\Frontend;

class InstantWinController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {
        $sg = $this->getGameService();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $game, $channel);
        if ($redirectFb) {
            return $redirectFb;
        }

        $user = $this->zfcUserAuthentication()->getIdentity();

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute(''. $game->getClassType() . '/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        if ($game->getOccurrenceType()=='datetime') {
            if ($this->getRequest()->isPost()) {
                // En post, je reçois la maj du form pour les gagnants. Je n'ai pas à créer une nouvelle participation mais vérifier la précédente
                $lastEntry = $sg->findLastInactiveEntry($game, $user);
                if (!$lastEntry) {
                    return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }
                $winner = $lastEntry->getWinner();
                // if not winner, I'm not authorized to call this page in POST mode.
                if (!$winner) {
                    return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                }
            } else {
                // J'arrive sur le jeu, j'essaie donc de participer
                $entry = $sg->play($game, $user);
                if (!$entry) {
                    // the user has already taken part of this game and the participation limit has been reached
                    $this->flashMessenger()->addMessage('Vous avez déjà participé');

                    return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
                }

                // update the winner attribute in entry.
                $winner = $sg->IsInstantWinner($game, $user);
            }
            $prize = null;
            if ($winner) {
                $prize = $winner->getPrize();
            }
            $viewVariables = array(
                'winner' => $winner,
                'prize' => $prize,
                'over' => false,
            );
        } elseif ($game->getOccurrenceType()=='code') {
            $form = $this->getServiceLocator()->get('playgroundgame_instantwinoccurrencecode_form');
            $form->setAttribute('action', $this->frontendUrl()->fromRoute('instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            $occurrence = null;
            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getPost());
                if ($form->isValid()) {
                    $data =  $form->getData('code-input');
                    $code = filter_var($data['code-input'], FILTER_SANITIZE_STRING);
                    $occurrence = $this->getGameService()->isInstantWinner($game, $user, $code);
                    if (!$occurrence) {
                        $this->flashMessenger()->addMessage('Le code entré est invalide ou a déjà été utilisé !');
                        return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
                    } else {
                        return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
                    }
                }
            }
            $viewVariables = array(
                'form' => $form,
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
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));
        }
        $winner = $lastEntry->getWinner();
        $occurrence = null;

        // On tente de récupèrer l'occurrence si elle existe pour avoir accés au lot associé
        $occurrences = $sg->getInstantWinOccurrenceMapper()->findBy(array('instantwin' => $game->getId(), 'entry' => $lastEntry->getId(), ));
        if (!empty($occurrences)) {
            $occurrence = current($occurrences);
        }

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute('instantwin/result', array('id' => $game->getIdentifier(), 'channel' => $channel)));
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute('instantwin', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        $statusMail = null;
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                if (isset($data['email1']) || isset($data['email2']) || isset($data['email3'])) {
                    $result = $this->getGameService()->sendShareMail($data, $game, $user, $lastEntry);
                    if ($result) {
                        $statusMail = true;
                    }
                }
            }
        }

        $prize = null;
        if ($occurrence instanceof \PlaygroundGame\Entity\InstantWinOccurrence) {
            $prize = $occurrence->getPrize();
        }
        
        // buildView must be before sendMail because it adds the game template path to the templateStack
        $viewModel = $this->buildView($game);
        
        $this->sendMail($game, $user, $lastEntry, $prize);

        if ($viewModel instanceof \Zend\View\Model\ViewModel) {
            $viewModel->setVariables(array(
                'occurrence'       => $occurrence,
                'statusMail'       => $statusMail,
                'winner'           => $winner,
                'form'             => $form,
                'socialLinkUrl'    => $socialLinkUrl,
                'secretKey'        => $secretKey,
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
