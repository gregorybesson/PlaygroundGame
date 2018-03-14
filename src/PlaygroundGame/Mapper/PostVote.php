<?php

namespace PlaygroundGame\Mapper;

class PostVote extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\PostVote');
        }

        return $this->er;
    }
}
