<?php
namespace PlaygroundGame\Controller\Frontend;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\Factory as InputFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

class QuizController extends GameController
{
    /**
     *
     * @var gameService
     */
    protected $gameService;

    public function __construct(ServiceLocatorInterface $locator)
    {
        parent::__construct($locator);
    }

    public function playAction()
    {
        // the quiz is done for the first time in this entry
        $firstTime = true;
        $playError = null;
        $entry = $this->getGameService()->play($this->game, $this->user, $playError);

        if (!$entry) {
            $reason = "";
            if ($playError === -1) {
                // the user has already taken part to this game and the participation limit has been reached
                $this->flashMessenger()->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate("You have already played", "playgroundgame"));
                $reason = '?playLimitReached=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType().'/result',
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            } elseif ($playError === -2) {
                // the user has not accepted the mandatory rules of the game
                $this->flashMessenger()->addMessage('Vous devez accepter le réglement');
                $reason = '?NoOptin=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType(),
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            } elseif ($playError === -3) {
                // the user has enough points to buy an entry to this game
                $this->flashMessenger()->addMessage("Vous ne pouvez pas acheter la partie");
                $reason = '?NotPaid=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType(),
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            } else {
                $this->flashMessenger()->addMessage("An error occurred. Please try again later");
                $reason = '?Error=1';
                $noEntryRedirect = $this->frontendUrl()->fromRoute(
                    $this->game->getClassType(),
                    array(
                        'id' => $this->game->getIdentifier(),
                    )
                ) .$reason;
            }

            return $this->redirect()->toUrl($noEntryRedirect);
        }

        $reply = $this->getGameService()->getQuizReplyMapper()->getLastGameReply($entry);
        $userAnswers = array();
        if ($reply) {
            $firstTime = false;
            foreach ($reply->getAnswers() as $answer) {
                $userAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                $userAnswers[$answer->getQuestionId()]['answer'] = $answer->getAnswer();
            }
        }

        $questions = $this->game->getQuestions();
        $totalQuestions = count($questions);

        $form = new Form();

        $inputFilter = new \Laminas\InputFilter\InputFilter();
        $factory = new InputFactory();

        $i = 0;
        $j = 0;
        $elementData = array();
        $explanations = array();
        $data = $this->getRequest()->getPost()->toArray();
        $anticheat = array();

        foreach ($questions as $q) {
            if (($this->game->getQuestionGrouping() > 0 && $i % $this->game->getQuestionGrouping() === 0)
                || ($i === 0 && $this->game->getQuestionGrouping() === 0)
            ) {
                $fieldsetName = 'questionGroup' . ++ $j;
                $fieldset = new Fieldset($fieldsetName);
                $fieldsetFilter = new \Laminas\InputFilter\InputFilter();
            }

            if ($this->getRequest()->isPost()) {
                $jsonData = json_decode($q->getJsonData(), true);
                // décalage de 2h avec  UTC
                $date = (isset($jsonData['stopdate'])) ? strtotime($jsonData['stopdate']) : false;

                if ($date) {
                    $now = time();
                    if ($now > $date) {
                        $anticheat[] = $q->getId();
                        continue;
                    }
                }
            }

            $name = 'q' . $q->getId();

            if ($q->getType() === 0) {
                $element = new Element\Radio($name);
                $values = array();
                $valuesSortedByPosition = array();
                $position = 0;
                foreach ($q->getAnswers() as $a) {
                    $status = (
                        isset($userAnswers[$q->getId()]) &&
                        isset($userAnswers[$q->getId()][$a->getId()])
                    )? true:false;
                    $pos = ($a->getPosition() == 0 && isset($values[$a->getPosition()])) ? $position : $a->getPosition();
                    $values[$pos] = array(
                        'id' => $a->getId(),
                        'position' => $pos,
                        'answer' => $a->getAnswer(),
                        'checked' => $status
                    );
                    $explanations[$a->getAnswer()] = $a->getExplanation();
                    ++$position;
                }
                ksort($values);
                foreach ($values as $key => $value) {
                    $valuesSortedByPosition[$value['id']] = $value['answer'];
                    if ($value['checked']) {
                        $element->setValue($value['id']);
                    }
                }
                $element->setValueOptions($valuesSortedByPosition);
                $element->setLabelOptions(array("disable_html_escape"=>true));
                $elementData[$q->getId()] = new Element\Hidden($name.'-data');
            } elseif ($q->getType() === 1 || $q->getType() === 3) {
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

            $fieldsetFilter->add(
                $factory->createInput(
                    [
                        'name'     => $name,
                        'required' => false,
                        'allowEmpty' => true,
                    ]
                )
            );

            // TODO: Add this filter when the option "is mandatory" is checked on the question in the BO
            // $fieldsetFilter->add(
            //     $factory->createInput(
            //         [
            //             'name'     => $name,
            //             'required' => true,
            //             'validators' => [
            //                 [
            //                     'name' =>'NotEmpty',
            //                     'options' => [
            //                         'messages' => [
            //                             'isEmpty' => 'Merci de répondre à la question.',
            //                         ],
            //                     ],
            //                 ],
            //             ]
            //         ]
            //     )
            // );

            $i ++;
            if (($this->game->getQuestionGrouping() > 0 && $i % $this->game->getQuestionGrouping() == 0 && $i > 0)
                || $i == $totalQuestions
            ) {
                $form->add($fieldset);
                $inputFilter->add($fieldsetFilter, $fieldsetName);
            }
        }

        $form->setInputFilter($inputFilter);

        if ($this->getRequest()->isPost()) {
            foreach ($anticheat as $id) {
                $j = 0;
                $i = 0;
                foreach ($questions as $q) {
                    if (($this->game->getQuestionGrouping() > 0 && $i % $this->game->getQuestionGrouping() == 0) || ($i == 0 && $this->game->getQuestionGrouping() == 0)) {
                        $fieldsetName = 'questionGroup' . ++ $j;
                    }
                    if ($q->getId() == $id) {
                        unset($data[$fieldsetName]['q'.$q->getId()]);
                    }
                    $i++;
                }
            }
            $action = $this->params('action');

            // On POST, if the anonymousUser has not been created yet, I try to create it now
            // Maybe is there only one form for the quiz and the player data... I try...
            // And if the formPlayer data was included in the form, I remove it
            if (!$this->user && $this->game->getAnonymousAllowed() && $this->game->getAnonymousIdentifier()) {
                $session = new \Laminas\Session\Container('anonymous_identifier');
                if (empty($session->offsetGet('anonymous_identifier'))) {
                    $controller = __NAMESPACE__ . '\\' . ucfirst($this->game->getClassType());
                    $registerUser  = $this->forward()->dispatch(
                        $controller,
                        array(
                            'action' => 'register',
                            'id'     => $this->game->getIdentifier()
                        )
                    );

                    foreach ($data as $index => $el) {
                        if (! is_array($el)) {
                            unset($data[$index]);
                        }
                    }
                    $playError = null;
                    $entry = $this->getGameService()->play($this->game, $this->user, $playError);
                    if (!$entry) {
                        $reason = "";
                        if ($playError === -1) {
                            // the user has already taken part to this game and the participation limit has been reached
                            $this->flashMessenger()->addMessage($this->getServiceLocator()->get('MvcTranslator')->translate("You have already played", "playgroundgame"));
                            $reason = '?playLimitReached=1';
                            $noEntryRedirect = $this->frontendUrl()->fromRoute(
                                $this->game->getClassType().'/result',
                                array(
                                    'id' => $this->game->getIdentifier(),
                                )
                            ) .$reason;
                        } elseif ($playError === -2) {
                            // the user has not accepted the mandatory rules of the game
                            $this->flashMessenger()->addMessage('Vous devez accepter le réglement');
                            $reason = '?NoOptin=1';
                            $noEntryRedirect = $this->frontendUrl()->fromRoute(
                                $this->game->getClassType(),
                                array(
                                    'id' => $this->game->getIdentifier(),
                                )
                            ) .$reason;
                        } elseif ($playError === -3) {
                            // the user has enough points to buy an entry to this game
                            $this->flashMessenger()->addMessage("Vous ne pouvez pas acheter la partie");
                            $reason = '?NotPaid=1';
                            $noEntryRedirect = $this->frontendUrl()->fromRoute(
                                $this->game->getClassType(),
                                array(
                                    'id' => $this->game->getIdentifier(),
                                )
                            ) .$reason;
                        }

                        return $this->redirect()->toUrl($noEntryRedirect);
                    }
                }
            }

            $form->setData($data);

            // Improve it : I don't validate the form in a timer quiz as no answer is mandatory
            if ($this->game->getTimer() || $form->isValid()) {
                unset($data['submitForm']);
                $entry = $this->getGameService()->createQuizReply($data, $this->game, $this->user);
            } else {
                // print_r($form->getMessages());
                // die();
            }

            return $this->redirect()->toUrl(
                $this->frontendUrl()->fromRoute(
                    $this->game->getClassType() . '/'. $this->game->nextStep($action),
                    array('id' => $this->game->getIdentifier())
                )
            );
        }

        $viewModel = $this->buildView($this->game);
        $viewModel->setVariables(
            [
                'entry' => $entry,
                'firstTime' => $firstTime,
                'questions' => $questions,
                'form'      => $form,
                'explanations' => $explanations,
                'flashMessages' => $this->flashMessenger()->getMessages(),
            ]
        );

        return $viewModel;
    }

    public function resultAction()
    {
        $playLimitReached = false;
        if ($this->getRequest()->getQuery()->get('playLimitReached')) {
            $playLimitReached = true;
        }
        $statusMail = null;
        $prediction = false;
        $userTimer = array();

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
        $nbQuestions = count($this->game->getQuestions());
        $nbCorrectQuestions = 0;
        $arCorrectQuestions = [];

        if ($reply !== null) {
            foreach ($reply->getAnswers() as $answer) {
                $q = $this->getGameService()->getQuizQuestionMapper()->findById($answer->getQuestionId());
                if ($answer->getCorrect()) {
                    $correctAnswers[$answer->getQuestionId()][$answer->getAnswerId()] = true;
                    $arCorrectQuestions[$answer->getQuestionId()] += 1;
                    if ($q->getMaxCorrectAnswers() == $arCorrectQuestions[$answer->getQuestionId()]) {
                        ++$nbCorrectQuestions;
                    }
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

        // The distribution of answers for each question
        $distribution = $this->getGameService()->getAnswersDistribution($this->game);

        // Je prépare le tableau des bonnes réponses trouvées et non trouvées
        $ga = array();
        $questions = $this->game->getQuestions();
        foreach ($questions as $q) {
            foreach ($q->getAnswers() as $a) {
                if ($a->getCorrect()) {
                    $ga[$q->getId()]['question'] = $q;
                    $ga[$q->getId()]['answers'][$a->getId()]['answer'] = $a;
                    $ga[$q->getId()]['answers'][$a->getId()]['answerText'] = $a->getAnswer();
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
                    $ga[$q->getId()]['answers'][$a->getId()]['answer'] = $a;
                    $ga[$q->getId()]['answers'][$a->getId()]['answerText'] = $a->getAnswer();
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

        // TODO: Change the way we know if the play step has been rejected
        $messages = $this->flashMessenger()->getMessages();
        if (!isset($messages[0]) || substr($messages[0], 0, 9) != 'Vous avez') {
            $this->getGameService()->sendMail($this->game, $this->user, $lastEntry);
        }

        $viewModel->setVariables(
            [
                'entry'               => $lastEntry,
                'statusMail'          => $statusMail,
                'form'                => $form,
                'winner'              => $winner,
                'prediction'          => $prediction,
                'userCorrectAnswers'  => $userCorrectAnswers,
                'maxCorrectAnswers'   => $maxCorrectAnswers,
                'ratioCorrectAnswers' => $ratioCorrectAnswers,
                'gameCorrectAnswers'  => $ga,
                'userTimer'           => $userTimer,
                'userAnswers'         => $userAnswers,
                'flashMessages'       => $this->flashMessenger()->getMessages(),
                'playLimitReached'    => $playLimitReached,
                'distribution'        => $distribution,
                'nbQuestions'         => $nbQuestions,
                'nbCorrectQuestions'  => $nbCorrectQuestions,
            ]
        );

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
        $response->setContent(\Laminas\Json\Json::encode(array(
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
        $response->setContent(\Laminas\Json\Json::encode(array(
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
        $response->setContent(\Laminas\Json\Json::encode(array(
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
        $response->setContent(\Laminas\Json\Json::encode(array(
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
