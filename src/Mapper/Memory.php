<?php

namespace PlaygroundGame\Mapper;

class Memory extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\Memory');
        }

        return $this->er;
    }
}
