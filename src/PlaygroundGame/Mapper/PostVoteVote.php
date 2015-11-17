<?php

namespace PlaygroundGame\Mapper;

use PlaygroundGame\Mapper\AbstractMapper;

class PostVoteVote extends AbstractMapper
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVoteVote');
        }

        return $this->er;
    }
}
