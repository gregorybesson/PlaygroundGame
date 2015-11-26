<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\QuizReply;
use PlaygroundGame\Entity\QuizReplyAnswer;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use Zend\Stdlib\ErrorHandler;

class Quiz extends Game implements ServiceManagerAwareInterface
{
    /**
     * @var QuizMapperInterface
     */
    protected $quizMapper;

    /**
     * @var QuizAnswerMapperInterface
     */
    protected $quizAnswerMapper;

    /**
     * @var QuizQuestionMapperInterface
     */
    protected $quizQuestionMapper;

    /**
     * @var QuizReplyMapperInterface
     */
    protected $quizReplyMapper;

    /**
     * @var quizReplyAnswerMapper
     */
    protected $quizReplyAnswerMapper;
    /**
     *
     *
     * @param  array                  $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function createQuestion(array $data)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $question  = new \PlaygroundGame\Entity\QuizQuestion();
        $form  = $this->getServiceManager()->get('playgroundgame_quizquestion_form');
        $form->bind($question);
        $form->setData($data);

        $quiz = $this->getGameMapper()->findById($data['quiz_id']);
        if (!$form->isValid()) {
            return false;
        }

        $question->setQuiz($quiz);

        // If question is a prediction, no need to calculate max good answers
        if (!$question->getPrediction()) {
            // Max points and correct answers calculation for the question
            if (!$question = $this->calculateMaxAnswersQuestion($question)) {
                return false;
            }
        }

        // Max points and correct answers recalculation for the quiz
        $quiz = $this->calculateMaxAnswersQuiz($question->getQuiz());

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('game' => $question, 'data' => $data));
        $this->getQuizQuestionMapper()->insert($question);
        $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('game' => $question, 'data' => $data));

        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $question->getId() . "-" . $data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path . $data['upload_image']['name']);
            $question->setImage($media_url . $data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        $this->getQuizQuestionMapper()->update($question);
        $this->getQuizMapper()->update($quiz);

        return $question;
    }

    /**
     * @param  array                  $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateQuestion(array $data, $question)
    {
        $path = $this->getOptions()->getMediaPath() . DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl() . '/';

        $form  = $this->getServiceManager()->get('playgroundgame_quizquestion_form');
        $form->bind($question);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        // If question is a prediction, no need to calculate max good answers
        if (!$question->getPrediction()) {
            // Max points and correct answers calculation for the question
            if (!$question = $this->calculateMaxAnswersQuestion($question)) {
                return false;
            }
        }

        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $question->getId() . "-" . $data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path . $data['upload_image']['name']);
            $question->setImage($media_url . $data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['delete_image']) && empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $image = $question->getImage();
            $image = str_replace($media_url, '', $image);
            if (file_exists($path .$image)) {
                unlink($path .$image);
            }
            $question->setImage(null);
            ErrorHandler::stop(true);
        }
        
        $i = 0;
        foreach ($question->getAnswers() as $answer) {
            if (!empty($data['answers'][$i]['upload_image']['tmp_name'])) {
                ErrorHandler::start();
                $data['answers'][$i]['upload_image']['name'] = $this->fileNewname(
                    $path,
                    $question->getId() . "-" . $data['answers'][$i]['upload_image']['name']
                );
                move_uploaded_file(
                    $data['answers'][$i]['upload_image']['tmp_name'],
                    $path . $data['answers'][$i]['upload_image']['name']
                );
                $answer->setImage($media_url . $data['answers'][$i]['upload_image']['name']);
                ErrorHandler::stop(true);
            }
            
            /*if(isset($data['delete_image']) && empty($data['upload_image']['tmp_name'])) {
                ErrorHandler::start();
                $image = $question->getImage();
                $image = str_replace($media_url, '', $image);
                unlink($path .$image);
                $question->setImage(null);
                ErrorHandler::stop(true);
            }*/
            
            $i++;
        }

        // Max points and correct answers recalculation for the quiz
        $quiz = $this->calculateMaxAnswersQuiz($question->getQuiz());

        // If the question was a pronostic, I update entries with the results !
        if ($question->getPrediction()) {
            // je recherche toutes les participations au jeu
            $entries = $this->getEntryMapper()->findByGameId($question->getQuiz());

            $answers = $question->getAnswers();

            $answersarray = array();
            foreach ($answers as $answer) {
                $answersarray[$answer->getId()] = $answer;
            }

            // I update all answers with points and correctness
            // Refactorer findByEntryAndQuestion pour qu'elle fonctionne avec QuizReplyAnswer
            /**
             * 1. Je recherche $this->getQuizReplyMapper()->findByEntry($entry)
             * 2. Pour chaque entrée trouvée, je recherche
             * $this->getQuizReplyAnswerMapper()->findByReplyAndQuestion($reply, $question->getId())
             * 3. Je mets à jour reply avec le nb de bonnes réponses
             * 4. Je trigger une story ?
             */
            foreach ($entries as $entry) {
                $quizReplies = $this->getQuizReplyMapper()->findByEntry($entry);
                if ($quizReplies) {
                    foreach ($quizReplies as $reply) {
                        $quizReplyAnswers = $this->getQuizReplyAnswerMapper()->findByReplyAndQuestion(
                            $reply,
                            $question->getId()
                        );
                        $quizPoints = 0;
                        $quizCorrectAnswers = 0;
                        if ($quizReplyAnswers) {
                            foreach ($quizReplyAnswers as $quizReplyAnswer) {
                                if (2 != $question->getType()) {
                                    if ($answersarray[$quizReplyAnswer->getAnswerId()]) {
                                        $updatedAnswer = $answersarray[$quizReplyAnswer->getAnswerId()];
                                        $quizReplyAnswer->setPoints($updatedAnswer->getPoints());
                                        $quizPoints += $updatedAnswer->getPoints();
                                        $quizReplyAnswer->setCorrect($updatedAnswer->getCorrect());
                                        $quizCorrectAnswers += $updatedAnswer->getCorrect();
                                        $quizReplyAnswer = $this->getQuizReplyAnswerMapper()->update(
                                            $quizReplyAnswer
                                        );
                                    }
                                } else {
                                    // question is a textarea
                                    // search for a matching answer
                                    foreach ($answers as $answer) {
                                        if (trim(strip_tags($answer->getAnswer())) == trim(
                                            strip_tags($quizReplyAnswer->getAnswer())
                                        )
                                        ) {
                                            $quizReplyAnswer->setPoints($answer->getPoints());
                                            $quizPoints += $answer->getPoints();
                                            $quizReplyAnswer->setCorrect($answer->getCorrect());
                                            $quizCorrectAnswers += $answer->getCorrect();
                                            $quizReplyAnswer = $this->getQuizReplyAnswerMapper()->update(
                                                $quizReplyAnswer
                                            );
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $winner = $this->isWinner($quiz, $quizCorrectAnswers);
                $entry->setWinner($winner);
                $entry->setPoints($quizPoints);
                $entry->setActive(false);
                $entry = $this->getEntryMapper()->update($entry);
            }

            $this->getEventManager()->trigger(
                __FUNCTION__.'.prediction',
                $this,
                array('question' => $question, 'data' => $data)
            );
        }

        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            array('question' => $question, 'data' => $data)
        );
        $this->getQuizQuestionMapper()->update($question);
        $this->getEventManager()->trigger(
            __FUNCTION__.'.post',
            $this,
            array('question' => $question, 'data' => $data)
        );

        $this->getQuizMapper()->update($quiz);

        return $question;
    }

    public function calculateMaxAnswersQuestion($question)
    {
        $question_max_points = 0;
        $question_max_correct_answers = 0;
        // Closed question : Only one answer allowed
        if ($question->getType() == 0) {
            foreach ($question->getAnswers() as $answer) {
                if ($answer->getPoints() > $question_max_points) {
                    $question_max_points = $answer->getPoints();
                }
                if ($answer->getCorrect() && $question_max_correct_answers==0) {
                    $question_max_correct_answers=1;
                }
            }
            if ($question_max_correct_answers == 0) {
                return false;
            }
        // Closed question : Many answers allowed
        } elseif ($question->getType() == 1) {
            foreach ($question->getAnswers() as $answer) {
                $question_max_points += $answer->getPoints();

                if ($answer->getCorrect()) {
                    ++$question_max_correct_answers;
                }
            }
            if ($question_max_correct_answers == 0) {
                return false;
            }
        // Not a question : A textarea to fill in
        } elseif ($question->getType() == 2) {
            $question_max_correct_answers = 0;
        }

        $question->setMaxPoints($question_max_points);
        $question->setMaxCorrectAnswers($question_max_correct_answers);

        return $question;
    }

    public function calculateMaxAnswersQuiz($quiz)
    {
        $question_max_points = 0;
        $question_max_correct_answers = 0;
        foreach ($quiz->getQuestions() as $question) {
            $question_max_points += $question->getMaxPoints();
            $question_max_correct_answers += $question->getMaxCorrectAnswers();
        }
        $quiz->setMaxPoints($question_max_points);
        $quiz->setMaxCorrectAnswers($question_max_correct_answers);

        return $quiz;
    }

    public function getNumberCorrectAnswersQuiz($user, $count = 'count')
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $query = $em->createQuery(
            "SELECT COUNT(e.id) FROM PlaygroundGame\Entity\Entry e, PlaygroundGame\Entity\Game g
                WHERE e.user = :user
                AND g.classType = 'quiz'
                AND e.points > 0"
        );
        $query->setParameter('user', $user);
        $number = $query->getSingleScalarResult();

        return $number;
    }

    public function createQuizReply($data, $game, $user)
    {
        // Si mon nb de participation est < au nb autorisé, j'ajoute une entry + reponses au quiz et points
        $quizReplyMapper = $this->getQuizReplyMapper();
        $entryMapper = $this->getEntryMapper();
        $entry = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return false;
        }

        $quizPoints          = 0;
        $quizCorrectAnswers  = 0;
        $maxCorrectAnswers = $game->getMaxCorrectAnswers();
        $totalQuestions = 0;

        $quizReply = new QuizReply();

        foreach ($data as $group) {
            foreach ($group as $q => $a) {
                if (strlen($q) > 5 && strpos($q, '-data', strlen($q) - 5) !== false) {
                    continue; // answer data is processed below
                }
                $question = $this->getQuizQuestionMapper()->findById((int) str_replace('q', '', $q));
                ++$totalQuestions;
                if (is_array($a)) {
                    foreach ($a as $k => $answer_id) {
                        $answer = $this->getQuizAnswerMapper()->findById($answer_id);
                        if ($answer) {
                            $quizReplyAnswer = new QuizReplyAnswer();
                            $quizReplyAnswer->setAnswer($answer->getAnswer());
                            $quizReplyAnswer->setAnswerId($answer_id);
                            $quizReplyAnswer->setQuestion($question->getQuestion());
                            $quizReplyAnswer->setQuestionId($question->getId());
                            $quizReplyAnswer->setPoints($answer->getPoints());
                            $quizReplyAnswer->setCorrect($answer->getCorrect());

                            $quizReply->addAnswer($quizReplyAnswer);
                            $quizPoints += $answer->getPoints();
                            $quizCorrectAnswers += $answer->getCorrect();

                            if (isset($group[$q.'-'.$answer_id.'-data'])) {
                                $quizReplyAnswer->setAnswerData($group[$q.'-'.$answer_id.'-data']);
                            }
                        }
                    }
                } elseif ($question->getType() == 0 || $question->getType() == 1) {
                    ++$totalQuestions;
                    $answer = $this->getQuizAnswerMapper()->findById($a);
                    if ($answer) {
                        $quizReplyAnswer = new QuizReplyAnswer();
                        $quizReplyAnswer->setAnswer($answer->getAnswer());
                        $quizReplyAnswer->setAnswerId($a);
                        $quizReplyAnswer->setQuestion($question->getQuestion());
                        $quizReplyAnswer->setQuestionId($question->getId());
                        $quizReplyAnswer->setPoints($answer->getPoints());
                        $quizReplyAnswer->setCorrect($answer->getCorrect());

                        $quizReply->addAnswer($quizReplyAnswer);
                        $quizPoints += $answer->getPoints();
                        $quizCorrectAnswers += $answer->getCorrect();
                        if (isset($group[$q.'-'.$a.'-data'])) {
                            $quizReplyAnswer->setAnswerData($group[$q.'-'.$a.'-data']);
                        }
                    }
                } elseif ($question->getType() == 2) {
                    ++$totalQuestions;
                    $quizReplyAnswer = new QuizReplyAnswer();
                    
                    $quizReplyAnswer->setAnswer($a);
                    $quizReplyAnswer->setAnswerId(0);
                    $quizReplyAnswer->setQuestion($question->getQuestion());
                    $quizReplyAnswer->setQuestionId($question->getId());
                    $quizReplyAnswer->setPoints(0);
                    $quizReplyAnswer->setCorrect(0);

                    $quizReply->addAnswer($quizReplyAnswer);
                    $quizPoints += 0;
                    $quizCorrectAnswers += 0;
                    $qAnswers = $question->getAnswers();
                    foreach ($qAnswers as $qAnswer) {
                        if (trim(strip_tags($a)) == trim(strip_tags($qAnswer->getAnswer()))) {
                            $quizReplyAnswer->setPoints($qAnswer->getPoints());
                            $quizPoints += $qAnswer->getPoints();
                            $quizReplyAnswer->setCorrect($qAnswer->getCorrect());
                            $quizCorrectAnswers += $qAnswer->getCorrect();
                            break;
                        }
                    }

                    if (isset($group[$q.'-'.$a.'-data'])) {
                        $quizReplyAnswer->setAnswerData($group[$q.'-'.$a.'-data']);
                    }
                }
            }
        }

        $winner = $this->isWinner($game, $quizCorrectAnswers);

        $entry->setWinner($winner);
        // Every winning participation is eligible to draw
        // Make this modifiable in the admin (choose who can participate to draw)
        $entry->setDrawable($winner);
        $entry->setPoints($quizPoints);
        $entry->setActive(false);
        $entry = $entryMapper->update($entry);

        $quizReply->setEntry($entry);
        $quizReply->setTotalCorrectAnswers($quizCorrectAnswers);
        $quizReply->setMaxCorrectAnswers($maxCorrectAnswers);
        $quizReply->setTotalQuestions($totalQuestions);

        $quizReplyMapper->insert($quizReply);

        $this->getEventManager()->trigger(
            'complete_quiz.post',
            $this,
            array('user' => $user, 'entry' => $entry, 'reply' => $quizReply, 'game' => $game)
        );

        return $entry;
    }

    public function isWinner($game, $quizCorrectAnswers = 0)
    {
        // Pour déterminer le gagnant, je regarde le nombre max de reponses correctes possibles
        // dans le jeu, puis je calcule le ratio de bonnes réponses et le compare aux conditions
        // de victoire
        $winner = false;
        $maxCorrectAnswers = $game->getMaxCorrectAnswers();
        if ($maxCorrectAnswers > 0) {
            $ratioCorrectAnswers = ($quizCorrectAnswers / $maxCorrectAnswers) * 100;
        } elseif ($game->getVictoryConditions() > 0) {
            // In the case I have a pronostic game for example
            $ratioCorrectAnswers = 0;
        } else {
            // In the case I want everybody to win
            $ratioCorrectAnswers = 100;
        }

        $winner = false;
        if ($game->getVictoryConditions() >= 0) {
            if ($ratioCorrectAnswers >= $game->getVictoryConditions()) {
                $winner = true;
            }
        }
        return $winner;
    }

    public function getEntriesHeader($game)
    {
        $header = parent::getEntriesHeader($game);
        $header['totalCorrectAnswers'] = 1;

        return $header;
    }

    public function getEntriesQuery($game)
    {
        $em = $this->getServiceManager()->get('doctrine.entitymanager.orm_default');

        $qb = $em->createQueryBuilder();
        $qb->select('
            r.id,
            u.username,
            u.title,
            u.firstname,
            u.lastname,
            u.email,
            u.optin,
            u.optinPartner,
            u.address,
            u.address2,
            u.postalCode,
            u.city,
            u.telephone,
            u.mobile,
            u.created_at,
            u.dob,
            e.winner,
            e.socialShares,
            e.playerData,
            e.updated_at,
            r.totalCorrectAnswers
            ')
            ->from('PlaygroundGame\Entity\QuizReply', 'r')
            ->innerJoin('r.entry', 'e')
            ->leftJoin('e.user', 'u')
            ->where($qb->expr()->eq('e.game', ':game'));
        
        $qb->setParameter('game', $game);

        return $qb->getQuery();
    }

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\Quiz;
    }

    /**
     * getQuizMapper
     *
     * @return QuizMapperInterface
     */
    public function getQuizMapper()
    {
        if (null === $this->quizMapper) {
            $this->quizMapper = $this->getServiceManager()->get('playgroundgame_quiz_mapper');
        }

        return $this->quizMapper;
    }

    /**
     * setQuizMapper
     *
     * @param  QuizMapperInterface $quizMapper
     * @return Game
     */
    public function setQuizMapper(GameMapperInterface $quizMapper)
    {
        $this->quizMapper = $quizMapper;

        return $this;
    }

    /**
     * getQuizQuestionMapper
     *
     * @return QuizQuestionMapperInterface
     */
    public function getQuizQuestionMapper()
    {
        if (null === $this->quizQuestionMapper) {
            $this->quizQuestionMapper = $this->getServiceManager()->get('playgroundgame_quizquestion_mapper');
        }

        return $this->quizQuestionMapper;
    }

    /**
     * setQuizQuestionMapper
     *
     * @param  QuizQuestionMapperInterface $quizquestionMapper
     * @return Quiz
     */
    public function setQuizQuestionMapper($quizquestionMapper)
    {
        $this->quizQuestionMapper = $quizquestionMapper;

        return $this;
    }

    /**
     * setQuizAnswerMapper
     *
     * @param  QuizAnswerMapperInterface $quizAnswerMapper
     * @return Quiz
     */
    public function setQuizAnswerMapper($quizAnswerMapper)
    {
        $this->quizAnswerMapper = $quizAnswerMapper;

        return $this;
    }

    /**
     * getQuizAnswerMapper
     *
     * @return QuizAnswerMapperInterface
     */
    public function getQuizAnswerMapper()
    {
        if (null === $this->quizAnswerMapper) {
            $this->quizAnswerMapper = $this->getServiceManager()->get('playgroundgame_quizanswer_mapper');
        }

        return $this->quizAnswerMapper;
    }

    /**
     * getQuizReplyMapper
     *
     * @return QuizReplyMapperInterface
     */
    public function getQuizReplyMapper()
    {
        if (null === $this->quizReplyMapper) {
            $this->quizReplyMapper = $this->getServiceManager()->get('playgroundgame_quizreply_mapper');
        }

        return $this->quizReplyMapper;
    }

    /**
     * setQuizReplyMapper
     *
     * @param  QuizReplyMapperInterface $quizreplyMapper
     * @return Quiz
     */
    public function setQuizReplyMapper($quizreplyMapper)
    {
        $this->quizReplyMapper = $quizreplyMapper;

        return $this;
    }

    /**
     * getQuizReplyAnswerMapper
     *
     * @return QuizReplyAnswerMapper
     */
    public function getQuizReplyAnswerMapper()
    {
        if (null === $this->quizReplyAnswerMapper) {
            $this->quizReplyAnswerMapper = $this->getServiceManager()->get('playgroundgame_quizreplyanswer_mapper');
        }

        return $this->quizReplyAnswerMapper;
    }

     /**
     * setQuizReplyAnswerMapper
     *
     * @param  QuizReplyAnswerMapper $quizReplyAnswerMapper
     * @return Quiz
     */
    public function setQuizReplyAnswerMapper($quizReplyAnswerMapper)
    {
        $this->quizReplyAnswerMapper = $quizReplyAnswerMapper;

        return $this;
    }
}
