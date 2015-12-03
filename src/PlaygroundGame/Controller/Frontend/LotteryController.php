<?php

namespace PlaygroundGame\Controller\Frontend;

class LotteryController extends GameController
{
    /**
     * @var gameService
     */
    protected $gameService;

    public function playAction()
    {
        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $this->game);
        if ($redirectFb) {
            return $redirectFb;
        }

        $user = $this->zfcUserAuthentication()->getIdentity();
        if (!$user && !$this->game->getAnonymousAllowed()) {
            $redirect = urlencode(
                $this->frontendUrl()->fromRoute(
                    $this->game->getClassType() . '/play',
                    array('id' => $this->game->getIdentifier(), ),
                    array('force_canonical' => true)
                )
            );

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'zfcuser/register',
                    array()
                ) . '?redirect='.$redirect
            );
        }

        $entry = $this->getGameService()->play($this->game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé');

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'lottery/result',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        // Every entry is eligible to draw
        $entry->setDrawable(true);
        $entry->setActive(false);
        $this->getGameService()->getEntryMapper()->update($entry);

        return $this->redirect()->toUrl(
            $this->frontendUrl()->fromRoute(
                $this->game->getClassType() . '/'. $this->game->nextStep($this->params('action')),
                array('id' => $this->game->getIdentifier())
            )
        );
    }

    public function resultAction()
    {
        $user = $this->zfcUserAuthentication()->getIdentity();
        $statusMail = null;
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'lottery',
            array('id' => $this->game->getIdentifier()),
            array('force_canonical' => true)
        ).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'lottery',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
                )
            );
        }

        if (!$user && !$this->game->getAnonymousAllowed()) {
            $redirect = urlencode(
                $this->frontendUrl()->fromRoute(
                    'lottery/result',
                    array(
                        'id' => $this->game->getIdentifier(),
                        
                    )
                )
            );
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'zfcuser/register',
                    array()
                ) . '?redirect='.$redirect
            );
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $this->game, $user, $lastEntry);
                if ($result) {
                    $statusMail = true;
                    $this->getGameService()->addAnotherChance($this->game, $user, 1);
                }
            }
        }

        // buildView must be before sendMail because it adds the game template path to the templateStack
        $viewModel = $this->buildView($this->game);
        
        $this->getGameService()->sendMail($this->game, $user, $lastEntry);

        $viewModel->setVariables(array(
                'statusMail'    => $statusMail,
                'form'          => $form,
                'socialLinkUrl' => $socialLinkUrl,
                'secretKey'     => $secretKey,
            ));

        return $viewModel;
    }

    public function fbshareAction()
    {
        $result = parent::fbshareAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
         )));

        return $response;
    }

    public function fbrequestAction()
    {
        $result = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function tweetAction()
    {
        $result = parent::tweetAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function googleAction()
    {
        $result = parent::googleAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $user, 1);
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
            'success' => $result,
            'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_lottery_service');
        }

        return $this->gameService;
    }
}
