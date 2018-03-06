<?php

namespace PlaygroundGame\Mapper;

class InstantWin extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\InstantWin');
        }

        return $this->er;
    }
}
