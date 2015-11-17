<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class QuizQuestion extends AbstractMapper
{

    public function findByGameId($quiz)
    {
        return $this->getEntityRepository()->findBy(array('quiz' => $quiz));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\QuizQuestion');
        }

        return $this->er;
    }
}
