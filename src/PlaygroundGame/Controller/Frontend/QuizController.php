<?php
namespace PlaygroundGame\Controller\Frontend;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;

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
        $sg         = $this->getGameService();

        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        $channel = $this->getEvent()->getRouteMatch()->getParam('channel');

        $game = $sg->checkGame($identifier);
        if (! $game || $game->isClosed()) {
            return $this->notFoundAction();
        }

        $redirectFb = $this->checkFbRegistration($this->zfcUserAuthentication()->getIdentity(), $game, $channel);
        if($redirectFb){
            return $redirectFb;
        }

        $user       = $this->zfcUserAuthentication()->getIdentity();

        if (!$user && !$game->getAnonymousAllowed()) {
            $redirect = urlencode($this->frontendUrl()->fromRoute($game->getClassType() . '/play', array('id' => $game->getIdentifier(), 'channel' => $channel), array('force_canonical' => true)));

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('zfcuser/register', array('channel' => $channel)) . '?redirect='.$redirect);
        }

        $entry = $sg->play($game, $user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé!');

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute($game->getClassType() . '/result',array('id' => $identifier, 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $questions = $game->getQuestions();
        $totalQuestions = count($questions);

        $form = new Form();

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();

        $i = 0;
        $j = 0;
        $elementData = array();
        $explanations = array();

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
                    $values[$a->getId()] = array(
                        'id' => $a->getId(),
                        'position' => $a->getPosition(),
                        'answer' => $a->getAnswer(),
                        );
                        $explanations[$a->getAnswer()] = $a->getExplanation();
                }
                sort($values);
                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                }
                $element->setValueOptions($valuesSortedByPosition);
                $element->setLabelOptions(array("disable_html_escape"=>true));

                $elementData[$q->getId()] = new Element\Hidden($name.'-data');

            } elseif ($q->getType() == 1) {
                $element = new Element\MultiCheckbox($name);
                $values = array();
                $valuesSortedByPosition = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getId()] = array(
                        'id' => $a->getId(),
                        'position' => $a->getPosition(),
                        'answer' => $a->getAnswer(),
                    );
                    $explanations[$a->getAnswer()] = $a->getExplanation();
                    $elementData[$a->getId()] = new Element\Hidden($name.'-'.$a->getId().'-data');
                }

                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                }

                $element->setValueOptions($valuesSortedByPosition);
                $element->setLabelOptions(array("disable_html_escape"=>true));

            } elseif ($q->getType() == 2) {
                $element = new Element\Textarea($name);
                $elementData[$q->getId()] = new Element\Hidden($name.'-data');
            }

            $element->setLabel($q->getQuestion());
            $fieldset->add($element);
            foreach ($elementData as $id => $e) {
                $fieldset->add($e);
            }

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

            // Improve it : I don't validate the form in a timer quiz as no answer is mandatory
            if ($game->getTimer() || $form->isValid()) {
                unset($data['submitForm']);
                $entry = $this->getGameService()->createQuizReply($data, $game, $user);
            }

            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute(''. $game->getClassType() . '/'. $game->nextStep($this->params('action')), array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel'))));
        }

        $viewModel = $this->buildView($game);
        $viewModel->setVariables(array(
            'game'      => $game,
            'questions' => $questions,
            'form'      => $form,
            'explanations' => $explanations,
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

        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()),0,15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute('quiz', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)).'?key='.$secretKey;
        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $sg->findLastInactiveEntry($game, $user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl($this->frontendUrl()->fromRoute('quiz', array('id' => $game->getIdentifier(), 'channel' => $this->getEvent()->getRouteMatch()->getParam('channel')), array('force_canonical' => true)));
        }

        // je compte les bonnes réponses et le ratio
        $maxCorrectAnswers = $game->getMaxCorrectAnswers();
        $winner = $lastEntry->getWinner();
        $replies    = $sg->getQuizReplyMapper()->getLastGameReply($lastEntry);
        $userCorrectAnswers = 0;
        $correctAnswers = array();
        $userAnswers = array();


        foreach ($replies as $reply) {
            foreach ($reply->getAnswers() as $answer) {
                if ($answer->getCorrect()) {
                    $correctAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                    ++$userCorrectAnswers;
                }
                $userAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                $userAnswers[$answer->getQuestionId()]['answer'] = $answer->getAnswer();
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
               'ratio'  => $ratioCorrectAnswers,
               'timer'  => $end - $start,
            );
        }

        // Je prépare le tableau des bonnes réponses trouvées et non trouvées
        $gameCorrectAnswers = array();
        $questions = $game->getQuestions();
        foreach ($questions as $q) {
            foreach ($q->getAnswers() as $a) {
                if ($a->getCorrect()) {
                    $gameCorrectAnswers[$q->getId()]['question'] = $q;
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['answer'] = $a->getAnswer();
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['explanation'] = $a->getExplanation();
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['userAnswer'] = isset($userAnswers[$q->getId()]) ? $userAnswers[$q->getId()]['answer'] : false;

                    if (isset($correctAnswers[$q->getId()]) && isset($correctAnswers[$q->getId()][$a->getId()])) {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['found'] = true;
                    } else {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['found'] = false;
                    }
                    
                    if (isset($userAnswers[$q->getId()]) && isset($userAnswers[$q->getId()][$a->getId()])) {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['yourChoice'] = true;
                    }else {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['yourChoice'] = false;
                    }

                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['correctAnswers'] = true;
                } else {
                    $gameCorrectAnswers[$q->getId()]['question'] = $q;
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['answer'] = $a->getAnswer();
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['explanation'] = $a->getExplanation();
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['correctAnswers'] = false;
                    $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['userAnswer'] = isset($userAnswers[$q->getId()]) ? $userAnswers[$q->getId()]['answer'] : false;
                    
                    if (isset($userAnswers[$q->getId()]) && isset($userAnswers[$q->getId()][$a->getId()])) {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['yourChoice'] = true;
                    }else {
                        $gameCorrectAnswers[$q->getId()]['answers'][$a->getId()]['yourChoice'] = false;
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

        // buildView must be before sendMail because it adds the game template path to the templateStack
        $viewModel = $this->buildView($game);
        
        $this->sendMail($game, $user, $lastEntry);

        $viewModel->setVariables(array(
            'entry'               => $lastEntry,
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
            'socialLinkUrl'       => $socialLinkUrl,
            'secretKey'           => $secretKey,
            'userTimer'           => $userTimer,
            'userAnswers'         => $userAnswers,
        ));

        return $viewModel;
    }

    public function fbshareAction()
    {
        $sg = $this->getGameService();
        $result = parent::fbshareAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            // Improve this thing
            $lastEntry = $sg->findLastInactiveEntry($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->addAnotherChance($game, $user, 1);
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

        if ($result->getVariable('success')) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->findLastInactiveEntry($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->addAnotherChance($game, $user, 1);
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

        if ($result->getVariable('success')) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->findLastInactiveEntry($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->addAnotherChance($game, $user, 1);
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

        if ($result->getVariable('success')) {
            $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
            $user = $this->zfcUserAuthentication()->getIdentity();
            $game = $sg->checkGame($identifier);
            $lastEntry = $sg->findLastInactiveEntry($game, $user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $sg->addAnotherChance($game, $user, 1);
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
}
