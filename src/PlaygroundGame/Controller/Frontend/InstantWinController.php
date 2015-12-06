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
        $redirectFb = $this->checkFbRegistration($this->user, $this->game);
        if ($redirectFb) {
            return $redirectFb;
        }

        if ($this->game->getOccurrenceType()=='datetime') {
            $entry = $this->getGameService()->play($this->game, $this->user);
            if (!$entry) {
                // the user has already taken part of this game and the participation limit has been reached
                $this->flashMessenger()->addMessage('Vous avez déjà participé');

                return $this->redirect()->toUrl(
                    $this->frontendUrl()->fromRoute(
                        'instantwin/result',
                        array('id' => $this->game->getIdentifier())
                    )
                );
            }

            // update the winner attribute in entry.
            $occurrence = $this->getGameService()->IsInstantWinner($this->game, $this->user);

            $viewVariables = array(
                'occurrence' => $occurrence,
                'entry' => $entry
            );
        } elseif ($this->game->getOccurrenceType()=='code') {
            $form = $this->getServiceLocator()->get('playgroundgame_instantwinoccurrencecode_form');
            $form->setAttribute(
                'action',
                $this->frontendUrl()->fromRoute(
                    'instantwin/play',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
                )
            );

            if ($this->getRequest()->isPost()) {
                $form->setData($this->getRequest()->getPost());
                if ($form->isValid()) {
                    $data =  $form->getData('code-input');
                    $code = filter_var($data['code-input'], FILTER_SANITIZE_STRING);
                    $occurrence = $this->getGameService()->isInstantWinner($this->game, $this->user, $code);
                    if (!$occurrence) {
                        $this->flashMessenger()->addMessage('Le code entré est invalide ou a déjà été utilisé !');
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute(
                                'instantwin/play',
                                array('id' => $this->game->getIdentifier()),
                                array('force_canonical' => true)
                            )
                        );
                    } else {
                        return $this->redirect()->toUrl(
                            $this->frontendUrl()->fromRoute(
                                'instantwin/result',
                                array('id' => $this->game->getIdentifier())
                            )
                        );
                    }
                }
            }
            $viewVariables = array('form' => $form);
        }
        
        $viewModel = $this->buildView($this->game);
        if ($viewModel instanceof \Zend\View\Model\ViewModel) {
            $viewModel->setVariables($viewVariables);
        }

        return $viewModel;
    }

    public function resultAction()
    {
        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);

        if (!$lastEntry) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'instantwin',
                    array('id' => $this->game->getIdentifier(), ),
                    array('force_canonical' => true)
                )
            );
        }
        $winner = $lastEntry->getWinner();
        $occurrence = null;

        // On tente de récupèrer l'occurrence si elle existe pour avoir accés au lot associé
        $occurrences = $this->getGameService()->getInstantWinOccurrenceMapper()->findBy(
            array('instantwin' => $this->game->getId(), 'entry' => $lastEntry->getId())
        );
        if (!empty($occurrences)) {
            $occurrence = current($occurrences);
        }

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'instantwin',
            array('id' => $this->game->getIdentifier(), ),
            array('force_canonical' => true)
        ).'?key='.$secretKey;
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
                    $result = $this->getGameService()->sendShareMail($data, $this->game, $this->user, $lastEntry);
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
        $viewModel = $this->buildView($this->game);
        
        $this->getGameService()->sendMail($this->game, $this->user, $lastEntry, $prize);

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
