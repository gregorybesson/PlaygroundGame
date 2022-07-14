<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\QuizReply;
use PlaygroundGame\Entity\QuizReplyAnswer;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;
use Laminas\Stdlib\ErrorHandler;

class Quiz extends Game
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
        $path      = $this->getOptions()->getMediaPath().DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl().'/';

        $question = new \PlaygroundGame\Entity\QuizQuestion();
        $form     = $this->serviceLocator->get('playgroundgame_quizquestion_form');
        $form->bind($question);
        $form->setData($data);

        $quiz = $this->getGameMapper()->findById($data['quiz_id']);
        if (!$form->isValid()) {
            return false;
        }

        $question->setQuiz($quiz);

        // Max points and correct answers calculation for the question
        if (!$question = $this->calculateMaxAnswersQuestion($question)) {
            return false;
        }

        // Max points and correct answers recalculation for the quiz
        $quiz = $this->calculateMaxAnswersQuiz($question->getQuiz());

        $this->getEventManager()->trigger(__FUNCTION__, $this, array('game' => $question, 'data' => $data));
        $this->getQuizQuestionMapper()->insert($question);
        $this->getEventManager()->trigger(__FUNCTION__ .'.post', $this, array('game' => $question, 'data' => $data));

        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $question->getId()."-".$data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path.$data['upload_image']['name']);
            $question->setImage($media_url.$data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        $this->getQuizQuestionMapper()->update($question);
        $this->getQuizMapper()->update($quiz);

        return $question;
    }

    /**
     * @param  array $data
     * @return \PlaygroundGame\Entity\Game
     */
    public function updateQuestion(array $data, $question)
    {
        $path      = $this->getOptions()->getMediaPath().DIRECTORY_SEPARATOR;
        $media_url = $this->getOptions()->getMediaUrl().'/';

        $form = $this->serviceLocator->get('playgroundgame_quizquestion_form');
        $form->bind($question);
        $form->setData($data);

        if (!$form->isValid()) {
            return false;
        }

        // Max points and correct answers calculation for the question
        if (!$question = $this->calculateMaxAnswersQuestion($question)) {
            return false;
        }

        if (!empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $data['upload_image']['name'] = $this->fileNewname(
                $path,
                $question->getId()."-".$data['upload_image']['name']
            );
            move_uploaded_file($data['upload_image']['tmp_name'], $path.$data['upload_image']['name']);
            $question->setImage($media_url.$data['upload_image']['name']);
            ErrorHandler::stop(true);
        }

        if (isset($data['delete_image']) && !empty($data['delete_image']) && empty($data['upload_image']['tmp_name'])) {
            ErrorHandler::start();
            $image = $question->getImage();
            $image = str_replace($media_url, '', $image);
            if (file_exists($path.$image)) {
                unlink($path.$image);
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
                    $question->getId()."-".$data['answers'][$i]['upload_image']['name']
                );
                move_uploaded_file(
                    $data['answers'][$i]['upload_image']['tmp_name'],
                    $path.$data['answers'][$i]['upload_image']['name']
                );
                $answer->setImage($media_url.$data['answers'][$i]['upload_image']['name']);
                ErrorHandler::stop(true);
            }
            $i++;
        }

        // Max points and correct answers recalculation for the quiz
        $quiz = $this->calculateMaxAnswersQuiz($question->getQuiz());

        // If the question was a pronostic, I update entries with the results !
        if ($question->getPrediction()) {
            $this->updatePrediction($question);
        }

        $this->getEventManager()->trigger(
            __FUNCTION__,
            $this,
            array('question' => $question, 'data' => $data)
        );
        $this->getQuizQuestionMapper()->update($question);
        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('question' => $question, 'data' => $data)
        );

        $this->getQuizMapper()->update($quiz);

        return $question;
    }

    public function findRepliesByGame($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');
        $qb = $em->createQueryBuilder();
        $qb->select('r')
           ->from('PlaygroundGame\Entity\QuizReply', 'r')
           ->innerJoin('r.entry', 'e')
           ->where('e.game = :game')
           ->setParameter('game', $game);
        $query = $qb->getQuery();

        $replies = $query->getResult();

        return $replies;
    }

    public function updatePrediction($question)
    {
        set_time_limit(0);
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        /* @var $dbal \Doctrine\DBAL\Connection */
        $dbal = $em->getConnection();

        $answers = $question->getAnswers();
        $victoryCondition = $question->getQuiz()->getVictoryConditions()/100;
        $maxCorrectAnswers = $question->getQuiz()->getMaxCorrectAnswers();
        $nbQuestionsWinner = $victoryCondition * $maxCorrectAnswers;

        // I update all the answers with points and correctness
        // Very fast (native query inside)
        if ($question->getType() == 2) {
            foreach ($answers as $answer) {
                $correct = ($answer->getCorrect() == 1)?$answer->getCorrect():0;
                $value  = trim(strip_tags($answer->getAnswer()));
                $points = ($correct)?$answer->getPoints():0;
                $sql    = "
                UPDATE game_quiz_reply_answer AS ra
                SET ra.points=IF(ra.answer=:answer, :points, 0),
                    ra.correct = IF(ra.answer=:answer, :isCorrect, 0)
                WHERE ra.question_id = :questionId
                ";
                $stmt = $dbal->prepare($sql);
                $stmt->execute(
                    array(
                        'answer'     => $value,
                        'points'     => $points,
                        'isCorrect'  => $correct,
                        'questionId' => $question->getId()
                    )
                );
            }
        } else {
            foreach ($answers as $answer) {
                $correct = ($answer->getCorrect() == 1)?$answer->getCorrect():0;
                $points = ($correct)?$answer->getPoints():0;
                $sql    = "
                UPDATE game_quiz_reply_answer AS ra
                SET ra.points=:points,
                    ra.correct = :isCorrect
                WHERE ra.question_id = :questionId
                    AND ra.answer_id = :answerId
                ";

                $stmt = $dbal->prepare($sql);
                $stmt->execute(
                    array(
                        'points'     => $points,
                        'isCorrect'  => $correct,
                        'questionId' => $question->getId(),
                        'answerId'   => $answer->getId()
                    )
                );
            }
        }

        // Entry update with points. WINNER is calculated also !
        $sql = "
        UPDATE game_entry as e
        INNER JOIN
        (
            SELECT e.id, SUM(ra.points) as points, SUM(ra.correct) as correct
            FROM game_entry as e
            INNER JOIN game_quiz_reply AS r ON r.entry_id = e.id
            INNER JOIN game_quiz_reply_answer AS ra ON ra.reply_id = r.id
            GROUP BY e.id
        ) i ON e.id = i.id
        SET e.points = i.points, e.winner = IF( i.correct >= :nbQuestionsWinner, 1, 0)
        WHERE e.game_id = :gameId
        ";

        $stmt = $dbal->prepare($sql);
        $stmt->execute(
            array(
                'gameId' => $question->getQuiz()->getId(),
                'nbQuestionsWinner' => $nbQuestionsWinner
            )
        );

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('question' => $question)
        );
    }
    /**
     * This function update the sort order of the questions in a Quiz
     * BEWARE : This function is time consuming (1s for 11 updates)
     * If you have many replies, switch to a  batch
     *
     * To improve performance, usage of DQL update
     * http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
     *
     * @param  string $data
     * @return boolean
     */
    public function updatePredictionOLD($question)
    {
        set_time_limit(0);
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $replies = $this->findRepliesByGame($question->getQuiz());

        $answers = $question->getAnswers($question->getQuiz());

        $answersarray = array();
        foreach ($answers as $answer) {
            $answersarray[$answer->getId()] = $answer;
        }

        foreach ($replies as $reply) {
            $quizPoints         = 0;
            $quizCorrectAnswers = 0;

            foreach ($reply->getAnswers() as $quizReplyAnswer) {
                if (2 != $question->getType() && $quizReplyAnswer->getQuestionId() === $question->getId()) {
                    if ($answersarray[$quizReplyAnswer->getAnswerId()]) {
                        $updatedAnswer = $answersarray[$quizReplyAnswer->getAnswerId()];
                        $quizReplyAnswer->setPoints($updatedAnswer->getPoints());
                        $quizReplyAnswer->setCorrect($updatedAnswer->getCorrect());
                        $q = $em->createQuery('
							UPDATE PlaygroundGame\Entity\QuizReplyAnswer a
              SET a.points = :points, a.correct=:isCorrect
              WHERE a.id=:answerId
						');
                        $q->setParameter('points', $updatedAnswer->getPoints());
                        $q->setParameter('isCorrect', $updatedAnswer->getCorrect());
                        $q->setParameter('answerId', $quizReplyAnswer->getId());
                        $q->execute();
                    }
                } elseif ($quizReplyAnswer->getQuestionId() === $question->getId()) {
                    // question is a textarea
                    // search for a matching answer
                    foreach ($answers as $answer) {
                        if (trim(strip_tags($answer->getAnswer())) == trim(
                            strip_tags($quizReplyAnswer->getAnswer())
                        )
                        ) {
                            $quizReplyAnswer->setPoints($answer->getPoints());
                            $quizReplyAnswer->setCorrect($answer->getCorrect());
                            $q = $em->createQuery('
                              UPDATE PlaygroundGame\Entity\QuizReplyAnswer a
                              SET a.points = :points, a.correct=:isCorrect
                              WHERE a.id=:answerId
                            ');
                            $q->setParameter('points', $updatedAnswer->getPoints());
                            $q->setParameter('isCorrect', $updatedAnswer->getCorrect());
                            $q->setParameter('answerId', $quizReplyAnswer->getId());
                            $q->execute();
                        } else {
                            $quizReplyAnswer->setPoints(0);
                            $quizReplyAnswer->setCorrect(false);
                            $q = $em->createQuery('
                              UPDATE PlaygroundGame\Entity\QuizReplyAnswer a
                              SET a.points = 0, a.correct = false
                              WHERE a.id=:answerId
                            ');
                            $q->setParameter('answerId', $quizReplyAnswer->getId());
                            $q->execute();
                        }
                    }
                }

                // The reply has been updated with correct answers and points for this question.
                // I count the whole set of points for this reply and update the entry
                if ($quizReplyAnswer->getCorrect()) {
                    $quizPoints += $quizReplyAnswer->getPoints();
                    $quizCorrectAnswers += $quizReplyAnswer->getCorrect();
                }
            }

            $winner = $this->isWinner($question->getQuiz(), $quizCorrectAnswers);
            $reply->getEntry()->setWinner($winner);
            $reply->getEntry()->setPoints($quizPoints);
            // The entry should be inactive : entry->setActive(false);
            $this->getEntryMapper()->update($reply->getEntry());
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('question' => $question)
        );
    }

    public function getAnswersDistribution($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        /* @var $dbal \Doctrine\DBAL\Connection */
        $dbal = $em->getConnection();
        $sql    = "
        select q.id as question_id, q.question, qa.id as answer_id, qa.answer, count(a.id) as count from game as g
        inner join game_quiz_question as q on g.id = q.quiz_id
        inner join game_quiz_answer as qa on q.id = qa.question_id
        left join game_quiz_reply_answer as a on a.answer_id = qa.id
        where g.id = :quizId
        group by q.id, qa.id
        ";

        $stmt = $dbal->prepare($sql);
        $stmt->execute(
            array(
                'quizId' => $game->getId()
            )
        );

        $query = $stmt->executeQuery();
        $rows = $query->fetchAllAssociative();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['question_id']][] = $row;
        }

        $this->getEventManager()->trigger(
            __FUNCTION__ .'.post',
            $this,
            array('game' => $game)
        );

        return $result;
    }

    /**
     * This function update the sort order of the questions in a Quiz
     *
     * @param  string $data
     * @return boolean
     */
    public function sortQuestion($data)
    {
        $arr = explode(",", $data);

        foreach ($arr as $k => $v) {
            $question = $this->getQuizQuestionMapper()->findById($v);
            $question->setPosition($k);
            $this->getQuizQuestionMapper()->update($question);
        }

        return true;
    }

    /**
     * This function update the sort order of the answers in a Quiz
     *
     * @param  string $data
     * @return boolean
     */
    public function sortAnswer($data)
    {
        $arr = explode(",", $data);

        foreach ($arr as $k => $v) {
            $answer = $this->getQuizAnswerMapper()->findById($v);
            $answer->setPosition($k);
            $this->getQuizAnswerMapper()->update($answer);
        }

        return true;
    }

    /**
     * @return string
     */
    public function calculateMaxAnswersQuestion($question)
    {
        $question_max_points          = 0;
        $question_max_correct_answers = 0;
        // Closed question : Only one answer allowed
        if ($question->getType() == 0) {
            foreach ($question->getAnswers() as $answer) {
                if ($answer->getPoints() > $question_max_points) {
                    $question_max_points = $answer->getPoints();
                }
                if ($answer->getCorrect() && $question_max_correct_answers == 0) {
                    $question_max_correct_answers = 1;
                }
            }
            // if ($question_max_correct_answers == 0) {
            //     return false;
            // }
            // Closed question : Many answers allowed
        } elseif ($question->getType() == 1 || $question->getType() == 3) {
            foreach ($question->getAnswers() as $answer) {
                $question_max_points += $answer->getPoints();

                if ($answer->getCorrect()) {
                    ++$question_max_correct_answers;
                }
            }
            // if ($question_max_correct_answers == 0) {
            //     return false;
            // }
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
        $question_max_points          = 0;
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
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

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
        $entryMapper     = $this->getEntryMapper();
        $entry           = $this->findLastActiveEntry($game, $user);

        if (!$entry) {
            return false;
        }

        $quizPoints         = 0;
        $quizCorrectAnswers = 0;
        $maxCorrectAnswers  = $game->getMaxCorrectAnswers();
        $totalQuestions     = 0;

        $quizReply = $this->getQuizReplyMapper()->getLastGameReply($entry);
        if (!$quizReply) {
            $quizReply = new QuizReply();
        } else {
            $quizReplyAnswered = [];
            foreach ($quizReply->getAnswers() as $answer) {
                $quizReplyAnswered[$answer->getQuestionId()] = $answer;
            }
        }

        foreach ($data as $group) {
            if (count($group) > 0) {
                foreach ($group as $q => $a) {
                    if (strlen($q) > 5 && strpos($q, '-data', strlen($q)-5) !== false) {
                        continue;// answer data is processed below
                    }
                    $question = $this->getQuizQuestionMapper()->findById((int) str_replace('q', '', $q));
                    ++$totalQuestions;
                    if (is_array($a)) {
                        foreach ($a as $k => $answer_id) {
                            $answer = $this->getQuizAnswerMapper()->findById($answer_id);
                            if ($answer) {
                                if (isset($quizReplyAnswered[$question->getId()])) {
                                    $this->getQuizReplyAnswerMapper()->remove($quizReplyAnswered[$question->getId()]);
                                }

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
                            if (isset($quizReplyAnswered[$question->getId()])) {
                                $this->getQuizReplyAnswerMapper()->remove($quizReplyAnswered[$question->getId()]);
                            }
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
                        if (isset($quizReplyAnswered[$question->getId()])) {
                            $this->getQuizReplyAnswerMapper()->remove($quizReplyAnswered[$question->getId()]);
                        }
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
        }
        // TODO : In the case of usage of stopdate, the calculation of quizCorrectAnswers
        // and quizPoints is incorrect
        $winner = $this->isWinner($game, $quizCorrectAnswers);

        $entry->setWinner($winner);
        // Every winning participation is eligible to draw
        // TODO : Make this modifiable in the admin (choose who can participate to draw)
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
            __FUNCTION__ .'.post',
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
        $winner            = false;
        $maxCorrectAnswers = $game->getMaxCorrectAnswers();
        if ($maxCorrectAnswers > 0) {
            $ratioCorrectAnswers = ($quizCorrectAnswers/$maxCorrectAnswers)*100;
        } elseif ($game->getVictoryConditions() > 0) {
            // In the case I have a pronostic game for example
            $ratioCorrectAnswers = 0;
        } else {
            // In the case I want everybody to win
            $ratioCorrectAnswers = 100;
        }

        if ($game->getVictoryConditions() >= 0) {
            if ($ratioCorrectAnswers >= $game->getVictoryConditions()) {
                $winner = true;
            }
        }
        return $winner;
    }

    /**
     * DEPRECATED
     */
    public function getEntriesHeader($game)
    {
        $header = parent::getEntriesHeader($game);
        $header['totalCorrectAnswers'] = 1;

        return $header;
    }


    public function getEntriesQuery($game)
    {
        $em = $this->serviceLocator->get('doctrine.entitymanager.orm_default');

        $qb = $em->createQueryBuilder();
        $qb->select(
            '
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
            '
        )
            ->from('PlaygroundGame\Entity\QuizReply', 'r')
            ->innerJoin('r.entry', 'e')
            ->leftJoin('e.user', 'u')
            ->where($qb->expr()->eq('e.game', ':game'));

        $qb->setParameter('game', $game);

        return $qb;
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
            $this->quizMapper = $this->serviceLocator->get('playgroundgame_quiz_mapper');
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
            $this->quizQuestionMapper = $this->serviceLocator->get('playgroundgame_quizquestion_mapper');
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
            $this->quizAnswerMapper = $this->serviceLocator->get('playgroundgame_quizanswer_mapper');
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
            $this->quizReplyMapper = $this->serviceLocator->get('playgroundgame_quizreply_mapper');
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
            $this->quizReplyAnswerMapper = $this->serviceLocator->get('playgroundgame_quizreplyanswer_mapper');
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
