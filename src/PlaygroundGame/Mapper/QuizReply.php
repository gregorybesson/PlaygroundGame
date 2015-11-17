<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class QuizReply extends AbstractMapper
{

    public function findByEntry($entry)
    {
        return $this->getEntityRepository()->findBy(array('entry' => $entry));
    }

    public function findByEntryAndQuestion($entry, $questionId)
    {
        return $this->getEntityRepository()->findBy(array('entry' => $entry, 'question_id' => $questionId));
    }

    /*
     * deprecated
     */
    public function getLastGameReply($entry)
    {
        return $this->getEntityRepository()->findBy(array('entry' => $entry));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\QuizReply');
        }

        return $this->er;
    }
}
