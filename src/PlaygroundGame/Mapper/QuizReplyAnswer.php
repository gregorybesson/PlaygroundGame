<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class QuizReplyAnswer extends AbstractMapper
{

    public function findByReply($reply)
    {
        return $this->getEntityRepository()->findBy(array('reply' => $reply));
    }

    public function findByReplyAndQuestion($reply, $questionId)
    {
        return $this->getEntityRepository()->findBy(array('reply' => $reply, 'question_id' => $questionId));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\QuizReplyAnswer');
        }

        return $this->er;
    }
}
