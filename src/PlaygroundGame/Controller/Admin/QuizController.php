<?php

namespace PlaygroundGame\Controller\Admin;

use PlaygroundGame\Entity\Quiz;
use PlaygroundGame\Entity\QuizQuestion;
use PlaygroundGame\Controller\Admin\GameController;
use PlaygroundGame\Service\Game as AdminGameService;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class QuizController extends GameController
{
    /**
     * @var GameService
     */
    protected $adminGameService;
    protected $quizReplyAnswerMapper;
    protected $quizReplyMapper;

    public function listQuestionAction()
    {
        $service = $this->getAdminGameService();
        $quizId = $this->getEvent()->getRouteMatch()->getParam('quizId');
        if (!$quizId) {
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $quiz = $service->getGameMapper()->findById($quizId);
        $questions = $service->getQuizQuestionMapper()->findByGameId($quizId);

        if (is_array($questions)) {
            $paginator = new \Zend\Paginator\Paginator(new \Zend\Paginator\Adapter\ArrayAdapter($questions));
        } else {
            $paginator = $questions;
        }

        $paginator->setItemCountPerPage(10);
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
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }

        $form = $this->getServiceLocator()->get('playgroundgame_quizquestion_form');
        $form->get('submit')->setAttribute('label', 'Ajouter');
        $form->get('quiz_id')->setAttribute('value', $quizId);
        $form->setAttribute(
            'action',
            $this->url()->fromRoute('admin/playgroundgame/quiz-question-add', array('quizId' => $quizId))
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

                return $this->redirect()->toRoute('admin/playgroundgame/quiz-question-list', array('quizId'=>$quizId));
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
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $question   = $service->getQuizQuestionMapper()->findById($questionId);
        $quizId     = $question->getQuiz()->getId();

        $form = $this->getServiceLocator()->get('playgroundgame_quizquestion_form');
        $form->get('submit')->setAttribute('label', 'Mettre à jour');
        $form->get('quiz_id')->setAttribute('value', $quizId);
        $form->setAttribute(
            'action',
            $this->url()->fromRoute('admin/playgroundgame/quiz-question-edit', array('questionId' => $questionId))
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

                return $this->redirect()->toRoute('admin/playgroundgame/quiz-question-list', array('quizId'=>$quizId));
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
            return $this->redirect()->toRoute('admin/playgroundgame/list');
        }
        $question   = $service->getQuizQuestionMapper()->findById($questionId);
        $quizId     = $question->getQuiz()->getId();

        $service->getQuizQuestionMapper()->remove($question);
        $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The question was created');

        return $this->redirect()->toRoute('admin/playgroundgame/quiz-question-list', array('quizId'=>$quizId));
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
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/create-quiz', array('gameId' => 0)));
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
            $game = $service->create($data, $quiz, 'playgroundgame_quiz_form');
            if ($game) {
                $this->flashMessenger()->setNamespace('playgroundgame')->addMessage('The game was created');

                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }
        $gameForm->setVariables(array('form' => $form, 'game' => $quiz));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Create quiz'));
    }

    public function editQuizAction()
    {
        $service = $this->getAdminGameService();
        $gameId = $this->getEvent()->getRouteMatch()->getParam('gameId');

        if (!$gameId) {
            return $this->redirect()->toRoute('admin/playgroundgame/create-quiz');
        }

        $game = $service->getGameMapper()->findById($gameId);
        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-game/quiz/quiz');

        $gameForm = new ViewModel();
        $gameForm->setTemplate('playground-game/game/game-form');

        $form   = $this->getServiceLocator()->get('playgroundgame_quiz_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgroundgame/edit-quiz', array('gameId' => $gameId)));
        $form->setAttribute('method', 'post');

        if ($game->getFbAppId()) {
            $appIds = $form->get('fbAppId')->getOption('value_options');
            $appIds[$game->getFbAppId()] = $game->getFbAppId();
            $form->get('fbAppId')->setAttribute('options', $appIds);
        }

        $gameOptions = $this->getAdminGameService()->getOptions();
        $gameStylesheet = $gameOptions->getMediaPath() . '/' . 'stylesheet_'. $game->getId(). '.css';
        if (is_file($gameStylesheet)) {
            $values = $form->get('stylesheet')->getValueOptions();
            $values[$gameStylesheet] = 'Style personnalisé de ce jeu';

            $form->get('stylesheet')->setAttribute('options', $values);
        }

        $form->bind($game);

        if ($this->getRequest()->isPost()) {
            $data = array_replace_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );
            if (empty($data['prizes'])) {
                $data['prizes'] = array();
            }
            $result = $service->edit($data, $game, 'playgroundgame_quiz_form');

            if ($result) {
                return $this->redirect()->toRoute('admin/playgroundgame/list');
            }
        }

        $gameForm->setVariables(array('form' => $form, 'game' => $game));
        $viewModel->addChild($gameForm, 'game_form');

        return $viewModel->setVariables(array('form' => $form, 'title' => 'Edit quiz'));
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

    public function getQuizReplyAnswerMapper()
    {
        if (!$this->quizReplyAnswerMapper) {
            $this->quizReplyAnswerMapper = $this->getServiceLocator()->get('playgroundgame_quizreplyanswer_mapper');
        }
        return $this->quizReplyAnswerMapper;
    }

    public function getQuizReplyMapper()
    {
        if (!$this->quizReplyMapper) {
            $this->quizReplyMapper = $this->getServiceLocator()->get('playgroundgame_quizreply_mapper');
        }
        return $this->quizReplyMapper;
    }
}
