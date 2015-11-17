<?php

namespace PlaygroundGame\Mapper;

class Lottery extends Game
{

    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Lottery');
        }

        return $this->er;
    }
}
