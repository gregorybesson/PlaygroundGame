<?php

namespace PlaygroundGame\Mapper;

class TreasureHunt extends Game
{
    public function getEntityRepository()
    {
        if (null === $this->er) {
            $this->er = $this->em->getRepository('PlaygroundGame\Entity\TreasureHunt');
        }

        return $this->er;
    }
}
