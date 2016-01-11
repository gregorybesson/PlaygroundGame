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

    public function playAction()
    {
        $redirectFb = $this->checkFbRegistration($this->user, $this->game);
        if ($redirectFb) {
            return $redirectFb;
        }

        $entry = $this->getGameService()->play($this->game, $this->user);
        if (!$entry) {
            // the user has already taken part of this game and the participation limit has been reached
            $this->flashMessenger()->addMessage('Vous avez déjà participé!');

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    $this->game->getClassType() . '/result',
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $reply = $this->getGameService()->getQuizReplyMapper()->getLastGameReply($entry);
        $userAnswers = array();
        if ($reply) {
            foreach ($reply->getAnswers() as $answer) {
                $userAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                $userAnswers[$answer->getQuestionId()]['answer'] = $answer->getAnswer();
            }
        }

        $questions = $this->game->getQuestions();
        $totalQuestions = count($questions);

        $form = new Form();

        $inputFilter = new \Zend\InputFilter\InputFilter();
        $factory = new InputFactory();

        $i = 0;
        $j = 0;
        $elementData = array();
        $explanations = array();

        foreach ($questions as $q) {
            if (($this->game->getQuestionGrouping() > 0 && $i % $this->game->getQuestionGrouping() === 0) ||
                ($i === 0 && $this->game->getQuestionGrouping() === 0)
            ) {
                $fieldsetName = 'questionGroup' . ++ $j;
                $fieldset = new Fieldset($fieldsetName);
            }
            $name = 'q' . $q->getId();
            $fieldsetFilter = new \Zend\InputFilter\InputFilter();
            if ($q->getType() === 0) {
                $element = new Element\Radio($name);
                $values = array();
                $valuesSortedByPosition = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getPosition()] = array(
                        'id' => $a->getId(),
                        'position' => $a->getPosition(),
                        'answer' => $a->getAnswer(),
                        );
                    $explanations[$a->getAnswer()] = $a->getExplanation();
                }
                ksort($values);
                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                }
                $element->setValueOptions($valuesSortedByPosition);
                $element->setLabelOptions(array("disable_html_escape"=>true));
                $elementData[$q->getId()] = new Element\Hidden($name.'-data');
            } elseif ($q->getType() === 1) {
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
                if (isset($userAnswers[$q->getId()])) {
                    $element->setValue($userAnswers[$q->getId()]['answer']);
                }
                $elementData[$q->getId()] = new Element\Hidden($name.'-data');
            }

            $element->setLabel($q->getQuestion());
            $fieldset->add($element);
            if (is_array($elementData)) {
                foreach ($elementData as $id => $e) {
                    $fieldset->add($e);
                }
            } else {
                $fieldset->add($elementData);
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
            if (($this->game->getQuestionGrouping() > 0 && $i % $this->game->getQuestionGrouping() == 0 && $i > 0) ||
                $i == $totalQuestions
            ) {
                $form->add($fieldset);
                $inputFilter->add($fieldsetFilter, $fieldsetName);
            }
        }

        $form->setInputFilter($inputFilter);

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);

            // Improve it : I don't validate the form in a timer quiz as no answer is mandatory
            if ($this->game->getTimer() || $form->isValid()) {
                unset($data['submitForm']);
                $entry = $this->getGameService()->createQuizReply($data, $this->game, $this->user);
            }

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    $this->game->getClassType() . '/'. $this->game->nextStep($this->params('action')),
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(array(
            'questions' => $questions,
            'form'      => $form,
            'explanations' => $explanations,
            'flashMessages' => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    public function resultAction()
    {
        $statusMail = null;
        $prediction = false;
        $userTimer = array();
        $secretKey = strtoupper(substr(sha1(uniqid('pg_', true).'####'.time()), 0, 15));
        $socialLinkUrl = $this->frontendUrl()->fromRoute(
            'quiz',
            array('id' => $this->game->getIdentifier()),
            array('force_canonical' => true)
        ).'?key='.$secretKey;

        // With core shortener helper
        $socialLinkUrl = $this->shortenUrl()->shortenUrl($socialLinkUrl);

        $lastEntry = $this->getGameService()->findLastEntry($this->game, $this->user);
        if (!$lastEntry) {
            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    'quiz',
                    array('id' => $this->game->getIdentifier()),
                    array('force_canonical' => true)
                )
            );
        }

        // je compte les bonnes réponses et le ratio
        $maxCorrectAnswers = $this->game->getMaxCorrectAnswers();
        $winner = $lastEntry->getWinner();
        $reply = $this->getGameService()->getQuizReplyMapper()->getLastGameReply($lastEntry);
        $userCorrectAnswers = 0;
        $correctAnswers = array();
        $userAnswers = array();
        
        foreach ($reply->getAnswers() as $answer) {
            if ($answer->getCorrect()) {
                $correctAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                ++$userCorrectAnswers;
            }
            $userAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
            $userAnswers[$answer->getQuestionId()]['answer'] = $answer->getAnswer();
        }

        $ratioCorrectAnswers = 0;
        if ($maxCorrectAnswers > 0) {
            $ratioCorrectAnswers = ($userCorrectAnswers / $maxCorrectAnswers) * 100;
        } else {
            $ratioCorrectAnswers = 100;
        }

        if ($this->game->getTimer()) {
            $timer = $this->getGameService()->getEntryMapper()->findOneBy(
                array('game' => $this->game, 'user'=> $this->user)
            );
            $start = $timer->getCreatedAt()->format('U');
            $end = $timer->getUpdatedAt()->format('U');
            $userTimer = array(
               'ratio'  => $ratioCorrectAnswers,
               'timer'  => $end - $start,
            );
        }

        // Je prépare le tableau des bonnes réponses trouvées et non trouvées
        $ga = array();
        $questions = $this->game->getQuestions();
        foreach ($questions as $q) {
            foreach ($q->getAnswers() as $a) {
                if ($a->getCorrect()) {
                    $ga[$q->getId()]['question'] = $q;
                    $ga[$q->getId()]['answers'][$a->getId()]['answer'] = $a->getAnswer();
                    $ga[$q->getId()]['answers'][$a->getId()]['explanation'] = $a->getExplanation();
                    $ga[$q->getId()]['answers'][$a->getId()]['userAnswer'] = isset($userAnswers[$q->getId()]) ?
                        $userAnswers[$q->getId()]['answer'] :
                        false;

                    if (isset($correctAnswers[$q->getId()]) && isset($correctAnswers[$q->getId()][$a->getId()])) {
                        $ga[$q->getId()]['answers'][$a->getId()]['found'] = true;
                    } else {
                        $ga[$q->getId()]['answers'][$a->getId()]['found'] = false;
                    }
                    
                    if (isset($userAnswers[$q->getId()]) && isset($userAnswers[$q->getId()][$a->getId()])) {
                        $ga[$q->getId()]['answers'][$a->getId()]['yourChoice'] = true;
                    } else {
                        $ga[$q->getId()]['answers'][$a->getId()]['yourChoice'] = false;
                    }

                    $ga[$q->getId()]['answers'][$a->getId()]['correctAnswers'] = true;
                } else {
                    $ga[$q->getId()]['question'] = $q;
                    $ga[$q->getId()]['answers'][$a->getId()]['answer'] = $a->getAnswer();
                    $ga[$q->getId()]['answers'][$a->getId()]['explanation'] = $a->getExplanation();
                    $ga[$q->getId()]['answers'][$a->getId()]['correctAnswers'] = false;
                    $ga[$q->getId()]['answers'][$a->getId()]['userAnswer'] = isset($userAnswers[$q->getId()]) ?
                        $userAnswers[$q->getId()]['answer'] :
                        false;
                    
                    if (isset($userAnswers[$q->getId()]) && isset($userAnswers[$q->getId()][$a->getId()])) {
                        $ga[$q->getId()]['answers'][$a->getId()]['yourChoice'] = true;
                    } else {
                        $ga[$q->getId()]['answers'][$a->getId()]['yourChoice'] = false;
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

        $viewModel = $this->buildView($this->game);
        
        $this->getGameService()->sendMail($this->game, $this->user, $lastEntry);

        $viewModel->setVariables(array(
            'entry'               => $lastEntry,
            'statusMail'          => $statusMail,
            'form'                => $form,
            'winner'              => $winner,
            'prediction'          => $prediction,
            'userCorrectAnswers'  => $userCorrectAnswers,
            'maxCorrectAnswers'   => $maxCorrectAnswers,
            'ratioCorrectAnswers' => $ratioCorrectAnswers,
            'gameCorrectAnswers'  => $ga,
            'socialLinkUrl'       => $socialLinkUrl,
            'secretKey'           => $secretKey,
            'userTimer'           => $userTimer,
            'userAnswers'         => $userAnswers,
            'flashMessages'       => $this->flashMessenger()->getMessages(),
        ));

        return $viewModel;
    }

    public function fbshareAction()
    {
        $result = parent::fbshareAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            // Improve this thing
            $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
        $result = parent::fbrequestAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
        $result = parent::tweetAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
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
        $result = parent::googleAction();
        $bonusEntry = false;

        if ($result->getVariable('success')) {
            $lastEntry = $this->getGameService()->findLastInactiveEntry($this->game, $this->user);
            if ($lastEntry && $lastEntry->getWinner()) {
                $bonusEntry = $this->getGameService()->addAnotherChance($this->game, $this->user, 1);
            }
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
        if (! $this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_quiz_service');
        }

        return $this->gameService;
    }
}
