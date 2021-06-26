<?php

namespace PlaygroundGame\Controller\Admin;

use Laminas\View\Model\JsonModel;
use PlaygroundGame\Entity\Quiz;
use PlaygroundGame\Entity\QuizQuestion;
use PlaygroundGame\Controller\Admin\GameController;
use PlaygroundGame\Service\Game as AdminGameService;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;

class QuizController extends GameController
{
    /**
     * @var \PlaygroundGame\Service\Game
     */
    protected $adminGameService;
    protected $quizReplyAnswerMapper;
    protected $quizReplyMapper;

    public function listQuestionAction()
    {
        $service = $this->getAdminGameService();
        $quizId = $this->getEvent()->getRouteMatch()->getParam('quizId');
        if (!$quizId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $quiz = $service->getGameMapper()->findById($quizId);
        $questions = $service->getQuizQuestionMapper()->findByGameId($quizId);

        if (is_array($questions)) {
            $paginator = new \Laminas\Paginator\Paginator(new \Laminas\Paginator\Adapter\ArrayAdapter($questions));
        } else {
            $paginator = $questions;
        }

        $paginator->setItemCountPerPage(500);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        return array(
            'questions' => $paginator,
            'quiz_id' => $quizId,
            'quiz' => $quiz,
        );
    }

    public function addQuestionAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/quiz/question');
        $service = $this->getAdminGameService();
        $quizId = $this->getEvent()->getRouteMatch()->getParam('quizId');

        if (!$quizId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }

        $form = $this->getServiceLocator()->get('playgroundgame_quizquestion_form');
        $form->get('submit')->setAttribute('label', 'Ajouter');
        $form->get('quiz_id')->setAttribute('value', $quizId);
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute('playgroundgame/quiz-question-add', array('quizId' => $quizId))
        );
        $form->setAttribute('method', 'post');

        $question = new QuizQuestion();
        $form->bind($question);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $question = $service->createQuestion($data);
            if ($question) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The question was created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/quiz-question-list', array('quizId'=>$quizId)));
            } else { // Creation failed
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage(
                    'The question was not updated - create at least one good answer'
                );
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'quiz_id' => $quizId, 'question_id' => 0));
    }

    public function editQuestionAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/quiz/question');

        $questionId = $this->getEvent()->getRouteMatch()->getParam('questionId');
        if (!$questionId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $question   = $service->getQuizQuestionMapper()->findById($questionId);
        $quizId     = $question->getQuiz()->getId();

        $form = $this->getServiceLocator()->get('playgroundgame_quizquestion_form');
        $form->get('submit')->setAttribute('label', 'Mettre à jour');
        $form->get('quiz_id')->setAttribute('value', $quizId);
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute('playgroundgame/quiz-question-edit', array('questionId' => $questionId))
        );
        $form->setAttribute('method', 'post');

        $form->bind($question);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $question = $service->updateQuestion($data, $question);
            if ($question) {
                // Redirect to list of games
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The question was updated');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/quiz-question-list', array('quizId'=>$quizId)));
            } else {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage(
                    'The question was not updated - create at least one good answer'
                );
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'quiz_id' => $quizId, 'question_id' => $questionId));
    }

    public function removeQuestionAction()
    {
        $service = $this->getAdminGameService();
        $questionId = $this->getEvent()->getRouteMatch()->getParam('questionId');
        if (!$questionId) {
            return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
        }
        $question   = $service->getQuizQuestionMapper()->findById($questionId);
        $quizId     = $question->getQuiz()->getId();

        $service->getQuizQuestionMapper()->remove($question);
        $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The question was created');

        return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/quiz-question-list', array('quizId'=>$quizId)));
    }

    public function sortQuestionAction()
    {
        $result = $this->getAdminGameService()->sortQuestion($this->params()->fromQuery('order'));
        $model = new JsonModel(array(
            'success' => $result,
        ));
        return $model->setTerminal(true);
    }

    public function createQuizAction()
    {
        $service = $this->getAdminGameService();
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/quiz/quiz');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $quiz = new Quiz();

        $form = $this->getServiceLocator()->get('playgroundgame_quiz_form');
        $form->bind($quiz);
        $form->get('submit')->setAttribute('label', 'Add');
        $form->setAttribute(
            'action',
            $this->adminUrl()->fromRoute(
                'playgroundgame/create-quiz',
                array('gameId' => 0)
            )
        );
        $form->setAttribute('method', 'post');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }
            $game = $service->createOrUpdate($data, $quiz, 'playgroundgame_quiz_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toUrl($this->adminUrl()->fromRoute('playgroundgame/list'));
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $quiz));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create quiz'));
    }

    public function editQuizAction()
    {
        $this->checkGame();

        return $this->editGame(
            'playground-game/quiz/quiz',
            'playgroundgame_quiz_form'
        );
    }

    public function getAdminGameService()
    {
        if (!$this->adminGameService) {
            $this->adminGameService = $this->getServiceLocator()->get('playgroundgame_quiz_service');
        }

        return $this->adminGameService;
    }

    public function setAdminGameService(AdminGameService $adminGameService)
    {
        $this->adminGameService = $adminGameService;

        return $this;
    }
}
