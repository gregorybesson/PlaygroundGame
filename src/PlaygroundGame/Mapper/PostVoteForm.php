<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PostVoteForm extends AbstractMapper
{

    public function findByGame($game)
    {
        return $this->getEntityRepository()->findOneBy(array('postvote' => $game));
    }

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVoteForm');
        }

        return $this->er;
    }
}
