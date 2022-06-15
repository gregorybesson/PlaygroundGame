<?php

namespace PlaygroundGame\Mapper;

class Crossword extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Crossword');
        }

        return $this->er;
    }
}
