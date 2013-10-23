<?php
namespace PlaygroundGame\Controller\Frontend;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\View\Model\ViewModel;

class QuizController extends GameController
{

    /**
     *
     * @var gameService
     */
    protected $gameService;

    /**
     * --DONE-- 1. try to change the Game Id (redirected to game home)
     * --DONE-- 2. try to modify questions (the form is recreated and verified
     * in the controller)
     * --DONE-- 3. don't answer to questions (form is checked controller side)
     * --DONE-- 4. try to game the chrono (time is checked server side)
     * --DONE-- 5. try to play again (check autized # of tries server side
     * 6. try to change answers
     * --DONE-- 7. essaie de répondre sans être inscrit (on le redirige vers la
     * home du jeu)
     */
    public function playAction ()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user       = $this->zfcUserAuthentication()->getIdentity();
        $sg         = $this->getGameService();

        $game = $sg->checkGame($identifier);
        if (! $game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        if (! $user) {
            // the user is not registered yet.
            $redirect = urlencode($this->url()->fromRoute('frontend/quiz/play', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register', array('channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))) . '?redirect='.$redirect);
        }

        $entry = $sg->play($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé!');

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $questions = $game->getQuestions();
        $totalQuestions = count($questions);

        // TODO : create a Form class to implement this form
        $form = new Form();

        // defaults validators removed
        //$form->setUseInputFilterDefaults(false);

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();
        
        $i = 0;
        $j = 0;
        foreach ($questions as $q) {
            if (($game->getQuestionGrouping() > 0 && $i % $game->getQuestionGrouping() == 0) || ($i == 0 && $game->getQuestionGrouping() == 0)) {
            	$fieldsetName = 'questionGroup' . ++ $j;
            	$fieldset = new Fieldset($fieldsetName);
            }
            $name = 'q' . $q->getId();
            $fieldsetFilter = new \Zend\InputFilter\InputFilter();
            if ($q->getType() == 0) {
            $element = new Element\Radio($name);
                $values = array();
                $valuesSortedByPosition = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getPosition()] = array(
                        'answer' => $a->getAnswer(),
                        'id' => $a->getId()
                    );
                }
                ksort($values);
                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                }
                $element->setValueOptions($valuesSortedByPosition);
            } elseif ($q->getType() == 1) {
                $element = new Element\MultiCheckbox($name);
                $values = array();
                $valuesSortedByPosition = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getPosition()] = array(
                        'answer' => $a->getAnswer(),
                        'id' => $a->getId()
                    );
                }
                ksort($values);
                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                }
                $element->setValueOptions($valuesSortedByPosition);
                $element->setValueOptions($values);
            } elseif ($q->getType() == 2) {
                $element = new Element\Textarea($name);
            }

            $element->setLabel($q->getQuestion());
            $fieldset->add($element);

            $fieldsetFilter->add($factory->createInput(array(
            	'name'     => $name,
            	'required' => true,
            	'validators'=>array(
           			array(
            			'name'=>'NotEmpty',
            			'options'=>array(
            				'messages'=>array(
            					'isEmpty' => 'Merci de répondre à la question.',	
            				),
            			),
            		),
            	)
            )));
            
            $i ++;
            if (($game->getQuestionGrouping() > 0 && $i % $game->getQuestionGrouping() == 0 && $i > 0) || $i == $totalQuestions) {
                $form->add($fieldset);
                $inputFilter->add($fieldsetFilter, $fieldsetName);
            }
        }

        $form->setInputFilter($inputFilter);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);

            // TODO : improve it : I don't validate the form in a timer quiz as no answer is mandatory
            if ($game->getTimer() || $form->isValid()) {
            	unset($data['submitForm']);
                $entry = $this->getGameService()->createQuizReply($data, $game, $this->zfcUserAuthentication()->getIdentity());

                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz/result', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
            }
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game' => $game,
            'questions' => $questions,
            'form' => $form,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    public function resultAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $user = $this->zfcUserAuthentication()->getIdentity();
        $sg = $this->getGameService();

        $statusMail = null;
        $prediction = false;
		$userTimer = array();

        $game = $sg->checkGame($identifier);
        if (!$game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $secretKey = strtoupper(substr(sha1($user->getId().'####'.time()),0,15));
        $socialLinkUrl = $this->url()->fromRoute('frontend/quiz', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
        }

        // je compte les bonnes réponses et le ratio
        $maxCorrectAnswers = $game->getMaxCorrectAnswers();
        $winner = $lastEntry->getWinner();
        $replies    = $sg->getQuizReplyMapper()->getLastGameReply($lastEntry);
        $userCorrectAnswers = 0;
        $correctAnswers = array();

        foreach ($replies as $reply) {
            foreach ($reply->getAnswers() as $answer) {
                if ($answer->getCorrect()) {
                    $correctAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                    ++$userCorrectAnswers;
                }
            }
        }

        $ratioCorrectAnswers = 0;
        if ($maxCorrectAnswers > 0) {
            $ratioCorrectAnswers = ($userCorrectAnswers / $maxCorrectAnswers) * 100;
        } else {
            $ratioCorrectAnswers = 100;
        }
		
		if($game->getTimer()){
			$timer = $sg->getEntryMapper()->findOneBy(array('game' => $game , 'user'=> $user));
			$start = $timer->getCreatedAt()->format('U');
			$end = $timer->getUpdatedAt()->format('U');
			$userTimer = array(
								'ratio' 	=> $ratioCorrectAnswers,
								'timer' 	=> $end - $start, 
								);
		}

        // Je prépare le tableau des bonnes réponses trouvées et non trouvées
        $gameCorrectAnswers = array();
        $questions = $game->getQuestions();
        foreach ($questions as $q) {
            foreach ($q->getAnswers() as $a) {
                if ($a->getCorrect()) {
                    $gameCorrectAnswers[$q->getId()]['question'] = $q->getQuestion();
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['answer'] = $a->getAnswer();
                    if (isset($correctAnswers[$q->getId()]) && isset($correctAnswers[$q->getId()][$a->getId()])) {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['found'] = true;
                    } else {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['found'] = false;
                    }
                }
            }
            // if only one question is a prediction, we can't determine if it's a winner or looser
            if ($q->getPrediction()) {
                $prediction = true;
            }
        }

        $form = $this->getServiceLocator()->get('playgroundgame_sharemail_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $result = $this->getGameService()->sendShareMail($data, $game, $user, 'share_game', null, $userTimer);
                if ($result) {
                    $statusMail = true;
                    if ($lastEntry->getWinner()) {
                        $bonusEntry = $sg->playBonus($game, $user, 1);
                    }
                }
            }

        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'statusMail'          => $statusMail,
            'game'                => $game,
            'flashMessages'       => $this->flashMessenger()->getMessages(),
            'form'                => $form,
            'winner'              => $winner,
            'prediction'          => $prediction,
            'userCorrectAnswers'  => $userCorrectAnswers,
            'maxCorrectAnswers'   => $maxCorrectAnswers,
            'ratioCorrectAnswers' => $ratioCorrectAnswers,
            'gameCorrectAnswers'  => $gameCorrectAnswers,
            'socialLinkUrl' 	  => $socialLinkUrl,
            'secretKey'		  	  => $secretKey,
            'userTimer' 		  => $userTimer,
        ));

        return $viewModel;
    }

    public function fbshareAction()
    {
        $sg = $this->getGameService();
        $result = parent::fbshareAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->playBonus($game, $user, 1);
            }
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
        $sg = $this->getGameService();
        $result = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->playBonus($game, $user, 1);
            }
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
        $sg = $this->getGameService();
        $result = parent::tweetAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->playBonus($game, $user, 1);
            }
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
        $sg = $this->getGameService();
        $result = parent::googleAction();
        $bonusEntry = false;

        if ($result) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->getEntryMapper()->findLastInactiveEntryById($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->playBonus($game, $user, 1);
            }
        }

        $response = $this->getResponse();
        $response->setContent(\Zend\Json\Json::encode(array(
                'success' => $result,
                'playBonus' => $bonusEntry
        )));

        return $response;
    }

    public function getGameService ()
    {
        if (! $this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_quiz_service');
        }

        return $this->gameService;
    }

    public function setGameService (GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }
}
