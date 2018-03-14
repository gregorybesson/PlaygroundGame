<?php

namespace PlaygroundGame\Mapper;

class Quiz extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Quiz');
        }

        return $this->er;
    }
}
