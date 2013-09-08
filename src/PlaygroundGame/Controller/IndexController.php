<?php

namespace PlaygroundGame\Controller;

use Zend\Form\Element;
use Zend\Form\Fieldset;
use Zend\Form\Form;

use PlaygroundGame\Entity\LeaderBoard;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     * @var leaderBoardService
     */
    protected $leaderBoardService;

    /**
     * @var gameService
     */
    protected $gameService;

    /*public function indexAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }

        $mapper = $this->getServiceLocator()->get('playgroundgame_game_mapper');
        $game = $mapper->findByIdentifier($identifier);

        if (!$game) {
            return $this->notFoundAction();
        }
        // if the user is already logged in, I check if he has already registered to the game
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $mapperLeader = $this->getServiceLocator()->get('playgroundgame_leaderboard_mapper');
            $subscribed = $mapperLeader->findBy(array('game' => $game, 'user' => $this->zfcUserAuthentication()->getIdentity()));
            if ($subscribed) {
                //$this->flashMessenger()->addMessage('Already registered');
            }
        }

        //$this->layout('layout/game-2columns-right');
        $viewModel = new ViewModel(
            array(
                'game' => $game,
                'flashMessages' => $this->flashMessenger()->getMessages()
            )
        );

        return $viewModel;
    }

    public function quizAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }
        $quizMapper = $this->getGameService()->getQuizMapper();
        $game = $quizMapper->findByIdentifier($identifier);
        $mapperLeader = $this->getLeaderBoardService()->getLeaderBoardMapper();

        if (!$game) {
            return $this->notFoundAction();
        }
        // if the user is already logged in, I check if he has already registered to the game
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $subscribed = $mapperLeader->findBy(array('game' => $game, 'user' => $this->zfcUserAuthentication()->getIdentity()));
            if ($subscribed) {
                $this->flashMessenger()->addMessage('Already registered');
            }
        }

        $viewModel = new ViewModel(
            array(
                'game' => $game,
                'flashMessages' => $this->flashMessenger()->getMessages()
            )
        );

        return $viewModel;
    }*/

    /**
     * --DONE-- 1. try to change the Game Id (on le redirige vers la home du jeu)
     * --DONE-- 2. try to modify questions (the form is recreated and verified in the controller)
     * --DONE-- 3. don't answer to questions (form is checked controller side)
     * 4. try to game the chrono
     * 5. try to play again
     * 6. try to change answers
     *  --DONE-- 7. essaie de répondre sans être inscrit (on le redirige vers la home du jeu)
     */
    /*public function quizQuestionsAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }

        $quizMapper = $this->getGameService()->getQuizMapper();
        $quiz = $quizMapper->findByIdentifier($identifier);

        if (!$quiz) {
            return $this->notFoundAction();
        }

        // Is the user registered ? If not, I redirect him to the game home
        $mapperLeader = $this->getLeaderBoardService()->getLeaderBoardMapper();
        $leaderBoard = $mapperLeader->findBy(array('user' => $this->zfcUserAuthentication()->getIdentity(), 'game' => $quiz));

        if ($leaderBoard == null) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz', array('id' => $identifier)));
        }

        $questions = $quiz->getQuestions();
        $totalQuestions = count($questions);

        // TODO : create a Form class to implement this form
        $form = new Form();

        $i=0;
        $j=0;
        foreach ($questions as $q) {
            if (($quiz->getQuestionGrouping()>0 && $i%$quiz->getQuestionGrouping() == 0) || ( $i == 0 && $quiz->getQuestionGrouping()==0)) {
                $fieldset = new Fieldset('questionGroup'.++$j);
            }
            if ($q->getType() == 0) {
                $element = new Element\Radio('q'.$q->getId());
                $values = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getId()] = $a->getAnswer();
                }
                $element->setValueOptions($values);
            } elseif ($q->getType() == 1) {
                $element = new Element\MultiCheckbox('q'.$q->getId());
                $values = array();
                foreach ($q->getAnswers() as $a) {
                    $values[$a->getId()] = $a->getAnswer();
                }
                $element->setValueOptions($values);
            } elseif ($q->getType() == 2) {
                $element = new Element\Textarea('q'.$q->getId());
            }

            $element->setLabel($q->getQuestion());
            $fieldset->add($element);

            $i++;
            if ( ($quiz->getQuestionGrouping()>0 && $i%$quiz->getQuestionGrouping() == 0 && $i>0) || $i == $totalQuestions) {
                $form->add($fieldset);
            }
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $entry = $this->getGameService()->createQuizReply($data, $quiz, $this->zfcUserAuthentication()->getIdentity());

                // determine the route where the user should go
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz') .  '/' . $quiz->getIdentifier() . '/quiz-result');
            }
        }

        $viewModel = new ViewModel(
            array(
                'game' => $quiz,
                'questions' => $questions,
                'form' => $form,
                'flashMessages' => $this->flashMessenger()->getMessages()
            )
        );

        return $viewModel;
    }

    public function quizResultAction()
    {
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }

        $quizMapper = $this->getGameService()->getQuizMapper();
        $quiz = $quizMapper->findByIdentifier($identifier);

        if (!$quiz) {
            return $this->notFoundAction();
        }

        // Is the user registered ? If not, I redirect him to the game home
        $mapperLeader = $this->getLeaderBoardService()->getLeaderBoardMapper();
        $leaderBoard = $mapperLeader->findBy(array('user' => $this->zfcUserAuthentication()->getIdentity(), 'game' => $quiz));

        if ($leaderBoard == null) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz', array('id' => $identifier)));
        }

        // Has the user finished the game ?
        $lastEntry = $this->getGameService()->getEntryMapper()->findBy(array('game' => $quiz, 'user' => $this->zfcUserAuthentication()->getIdentity()), array('created_at' => 'DESC'), 1, 0);

        if ($lastEntry == null) {
            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz', array('id' => $identifier)));
        }

        $viewModel = new ViewModel(
                array(
                        'entry' => $lastEntry,
                        'flashMessages' => $this->flashMessenger()->getMessages()
                )
        );

        return $viewModel;
    }


    public function instantwinAction()
    {
        return new ViewModel();
    }
    public function instantwinparticipateAction()
    {
        return new ViewModel();
    }
    public function photocontestconsultationAction()
    {
        return new ViewModel();
    }
    public function photocontestcreateAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $file = $this->getRequest()->getFiles('file');
            $id = $this->getRequest()->getPost('id');
            if (isset($file)) {
                $response = '<h6 class="green">Photo 1</h6><div class="progress progress-striped progress-success active"><div class="bar" style="width:100%;"></div></div>';
                $response .= $this->upload_file('/public/uploads/photos/', $file);
                $response .= '<script>parent.document.getElementById("photoslide'.$id.'").innerHTML="'.addslashes($response).'";</script>';
                $response .= '<div id="uploaded">1</div>';
            }

            return new ViewModel(
                array(
                    'response' => $response
                )
            );
        } else {
            return new ViewModel();
        }

    }
    public function photocontestoverviewAction()
    {
        return new ViewModel();
    }
    public function photokitchenconsultationAction()
    {
        return new ViewModel();
    }
    public function photokitchenparticipateAction()
    {
        return new ViewModel();
    }
    public function postvoteAction()
    {
        return new ViewModel();
    }
    public function postvoteconsultationAction()
    {
        return new ViewModel();
    }
    public function postvotenotloggedAction()
    {
        return new ViewModel();
    }
    public function postvoteparticipationinscriptionAction()
    {
        return new ViewModel();
    }
    public function postvoteparticipationAction()
    {
        return new ViewModel();
    }
    public function postvotevalidationAction()
    {
        return new ViewModel();
    }
    public function postvoteinvitationAction()
    {
        return new ViewModel();
    }
    public function postvoterecirculationAction()
    {
        return new ViewModel();
    }

    public function registerAction()
    {
        $data = array();
        $identifier = $this->getEvent()->getRouteMatch()->getParam('id');
        if (!$identifier) {
            return $this->notFoundAction();
        }

        $gameMapper     = $this->getGameService()->getGameMapper();
        $mapperLeader   = $this->getLeaderBoardService()->getLeaderBoardMapper();
        $game           = $gameMapper->findByIdentifier($identifier);

        if (!$game) {
            return $this->notFoundAction();
        }

        // Is the user logged in ?
        if ($this->zfcUserAuthentication()->hasIdentity()) {
            $user = $this->zfcUserAuthentication()->getIdentity();
            $leaderBoard = $mapperLeader->findBy(array('user' => $user, 'game' => $game));

            //is the user registered ?
            if ($leaderBoard == null) {
                // The user is not registered, we register him
                $leaderBoard = new LeaderBoard();
                $leaderBoard->setGame($game);
                $leaderBoard->setUser($this->zfcUserAuthentication()->getIdentity());
                $leaderBoard->setPoints(100);

                try {
                    $result = $this->getLeaderBoardService()->create($leaderBoard);
                } catch (Exception $e) {
                    $this->fail($e->getMessage());
                }
            }

            // the user is then registered, I can redirect him to the game
            if ($game->getClassType() == 'quiz') {
                return $this->redirect()->toUrl($this->url()->fromRoute('frontend/quiz') .  '/' . $game->getIdentifier() . '/quiz-questions');
            } else {
                return $this->redirect()->toUrl($this->url()->fromRoute('game', array('id' => $identifier)));
            }
        } else {
            // The user is not logged in. I redirect him to the registration page
            //$this->flashMessenger()->addMessage('Please login or register to subscribe to this game');
            $redirect = $this->url()->fromRoute('game', array('id' => $identifier));

            return $this->redirect()->toUrl($this->url()->fromRoute('frontend/zfcuser/register') . ($redirect ? '?redirect='.$redirect : ''));
        }
    }

    public function getGameService()
    {
        if (!$this->gameService) {
            $this->gameService = $this->getServiceLocator()->get('playgroundgame_game_service');
        }

        return $this->gameService;
    }

    public function setGameService(GameService $gameService)
    {
        $this->gameService = $gameService;

        return $this;
    }

    public function getLeaderBoardService()
    {
        if (!$this->leaderBoardService) {
            $this->leaderBoardService = $this->getServiceLocator()->get('playgroundgame_leaderboard_service');
        }

        return $this->leaderBoardService;
    }

    public function setLeaderBoardService(LeaderBoardService $leaderBoardService)
    {
        $this->leaderBoardService = $leaderBoardService;

        return $this;
    }

    public function upload_file($path, $file)
    {
        $err = $file["error"];
        $message='<div style="margin:5px; color:red;">Upload fails! ';
        if ($err > 0) {
            switch ($err) {
                case '1':
                    $message.='Max file size exceeded. (php.ini)';
                    break;
                case '2':
                    $message.='Max file size exceeded.';
                    break;
                case '3':
                    $message.='File upload was only partial.';
                    break;
                case '4':
                    $message.='No file was attached.';
                    break;
                case '7':
                    $message.='File permission denied.';
                    break;
                default :
                    $message.='Unexpected error occurs.';}
            $message.='</div>';
        } else {

            if (file_exists($path.$file["name"])) {
                $message.='File already exist.</div>';} else {
                @move_uploaded_file($file["tmp_name"],$path.$file["name"]);
                $message='<div style="margin:5px; color:green;"><img src="'.$path.$file["name"].'"></div>';}
        }

        return $message;
    }*/
}
