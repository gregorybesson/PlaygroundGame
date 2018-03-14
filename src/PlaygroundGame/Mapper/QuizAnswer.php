<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class QuizAnswer extends AbstractMapper
{
    public function findByGameId($quiz)
    {
        return $this->getEntityRepository()->findBy(array('quiz' => $quiz));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\QuizAnswer');
        }

        return $this->er;
    }
}
